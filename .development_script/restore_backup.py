#!/usr/bin/env python3
"""
PostgreSQL Backup Restore Script

This script restores PostgreSQL backups created with pg_dump into a target database.
Supports both SQL text format and custom format backups.

db_source_connection_string=postgres://uc5pl6kh8bc503:pec46440951d1063376567b29f4e52a30f453f8f73d3b23bb6eba7a10f0662392@c6b7lkfdshud3i.cluster-czz5s0kz4scl.eu-west-1.rds.amazonaws.com:5432/dnd8telvkf2h0
db_target_connection_string=postgresql://u9hbadeilu1dbi:p4256fcbc2c877c8b63cd65efd4d0f2b936fde41ff45cde930e71da081a4937d3@cee3ebbhveeoab.cluster-czrs8kj4isg7.us-east-1.rds.amazonaws.com:5432/d38k4o46qhvi27

Usage:
    python restore_backup.py --conn-string "postgresql://user:pass@host:port/dbname" --backup-file backup.sql
    python restore_backup.py --conn-string "postgresql://user:pass@host:port/dbname" --backup-file backup.custom --format custom
"""

import argparse
import subprocess
import sys
import os
from urllib.parse import urlparse
import tempfile
import logging

# Set up logging
logging.basicConfig(level=logging.INFO, format='%(asctime)s - %(levelname)s - %(message)s')
logger = logging.getLogger(__name__)


def parse_connection_string(conn_string):
    """Parse PostgreSQL connection string and return connection parameters."""
    try:
        parsed = urlparse(conn_string)
        return {
            'host': parsed.hostname or 'localhost',
            'port': parsed.port or 5432,
            'database': parsed.path.lstrip('/') if parsed.path else '',
            'username': parsed.username or '',
            'password': parsed.password or ''
        }
    except Exception as e:
        raise ValueError(f"Invalid connection string format: {e}")


def create_pgpass_file(conn_params):
    """Create a temporary .pgpass file to avoid password prompts."""
    if not conn_params['password']:
        return None
    
    try:
        # Create temporary .pgpass file
        pgpass_fd, pgpass_path = tempfile.mkstemp(text=True)
        with os.fdopen(pgpass_fd, 'w') as f:
            f.write(f"{conn_params['host']}:{conn_params['port']}:{conn_params['database']}:{conn_params['username']}:{conn_params['password']}\n")
        
        # Set proper permissions (readable only by owner)
        os.chmod(pgpass_path, 0o600)
        return pgpass_path
    except Exception as e:
        logger.error(f"Failed to create .pgpass file: {e}")
        return None


def restore_sql_backup(backup_file, conn_params, drop_existing=False):
    """Restore from SQL text backup using psql."""
    logger.info(f"Restoring SQL backup from {backup_file}")
    
    # Build psql command
    cmd = [
        'psql',
        '-h', conn_params['host'],
        '-p', str(conn_params['port']),
        '-U', conn_params['username'],
        '-d', conn_params['database'],
        '-f', backup_file,
        '-v', 'ON_ERROR_STOP=1',  # Stop on first error
        '--single-transaction'    # Run in single transaction
    ]
    
    if drop_existing:
        logger.warning("Drop existing option not directly supported for SQL format. Consider using --clean option in pg_dump.")
    
    return cmd


def restore_custom_backup(backup_file, conn_params, drop_existing=False, jobs=1):
    """Restore from custom format backup using pg_restore."""
    logger.info(f"Restoring custom backup from {backup_file}")
    
    # Build pg_restore command
    cmd = [
        'pg_restore',
        '-h', conn_params['host'],
        '-p', str(conn_params['port']),
        '-U', conn_params['username'],
        '-d', conn_params['database'],
        '-v',  # Verbose output
        '--single-transaction'  # Run in single transaction
    ]
    
    if drop_existing:
        cmd.append('--clean')  # Drop existing objects before recreating
    
    if jobs > 1:
        cmd.extend(['-j', str(jobs)])  # Parallel jobs
    
    cmd.append(backup_file)
    return cmd


def run_restore_command(cmd, pgpass_path=None):
    """Execute the restore command with proper environment setup."""
    env = os.environ.copy()
    
    # Set PGPASSFILE if we created one
    if pgpass_path:
        env['PGPASSFILE'] = pgpass_path
    
    try:
        logger.info(f"Executing command: {' '.join(cmd)}")
        result = subprocess.run(
            cmd,
            env=env,
            capture_output=True,
            text=True,
            check=True
        )
        
        if result.stdout:
            logger.info("Command output:")
            print(result.stdout)
        
        logger.info("Restore completed successfully!")
        return True
        
    except subprocess.CalledProcessError as e:
        logger.error(f"Restore failed with exit code {e.returncode}")
        if e.stdout:
            logger.error(f"STDOUT: {e.stdout}")
        if e.stderr:
            logger.error(f"STDERR: {e.stderr}")
        return False
    except FileNotFoundError:
        logger.error("pg_restore or psql command not found. Make sure PostgreSQL client tools are installed and in PATH.")
        return False


def detect_backup_format(backup_file):
    """Try to detect the backup format by examining the file."""
    try:
        with open(backup_file, 'rb') as f:
            header = f.read(5)
            if header == b'PGDMP':
                return 'custom'
            elif header.startswith(b'--'):
                return 'sql'
            else:
                # Try to read as text and look for SQL patterns
                f.seek(0)
                try:
                    first_line = f.read(100).decode('utf-8', errors='ignore')
                    if any(keyword in first_line.upper() for keyword in ['CREATE', 'INSERT', 'SELECT', '--']):
                        return 'sql'
                except:
                    pass
                return 'custom'  # Default assumption
    except Exception as e:
        logger.warning(f"Could not detect backup format: {e}. Assuming custom format.")
        return 'custom'


def main():
    parser = argparse.ArgumentParser(description='Restore PostgreSQL backup to database')
    parser.add_argument('--conn-string', '-c', required=True,
                       help='PostgreSQL connection string (e.g., postgresql://user:pass@host:port/dbname)')
    parser.add_argument('--backup-file', '-f', required=True,
                       help='Path to backup file')
    parser.add_argument('--format', choices=['sql', 'custom', 'auto'], default='auto',
                       help='Backup format (default: auto-detect)')
    parser.add_argument('--drop-existing', '-d', action='store_true',
                       help='Drop existing database objects before restore (custom format only)')
    parser.add_argument('--jobs', '-j', type=int, default=1,
                       help='Number of parallel jobs for custom format restore (default: 1)')
    parser.add_argument('--verbose', '-v', action='store_true',
                       help='Enable verbose output')
    
    args = parser.parse_args()
    
    if args.verbose:
        logging.getLogger().setLevel(logging.DEBUG)
    
    # Validate backup file exists
    if not os.path.isfile(args.backup_file):
        logger.error(f"Backup file not found: {args.backup_file}")
        sys.exit(1)
    
    try:
        # Parse connection string
        conn_params = parse_connection_string(args.conn_string)
        logger.info(f"Connecting to database: {conn_params['database']} at {conn_params['host']}:{conn_params['port']}")
        
        # Detect or use specified format
        backup_format = args.format
        if backup_format == 'auto':
            backup_format = detect_backup_format(args.backup_file)
            logger.info(f"Detected backup format: {backup_format}")
        
        # Create .pgpass file if needed
        pgpass_path = create_pgpass_file(conn_params)
        
        try:
            # Build restore command based on format
            if backup_format == 'sql':
                cmd = restore_sql_backup(args.backup_file, conn_params, args.drop_existing)
            else:  # custom format
                cmd = restore_custom_backup(args.backup_file, conn_params, args.drop_existing, args.jobs)
            
            # Execute restore
            success = run_restore_command(cmd, pgpass_path)
            
            if success:
                logger.info("Database restore completed successfully!")
                sys.exit(0)
            else:
                logger.error("Database restore failed!")
                sys.exit(1)
                
        finally:
            # Clean up temporary .pgpass file
            if pgpass_path and os.path.exists(pgpass_path):
                os.remove(pgpass_path)
                
    except Exception as e:
        logger.error(f"Error during restore: {e}")
        sys.exit(1)


if __name__ == '__main__':
    main()