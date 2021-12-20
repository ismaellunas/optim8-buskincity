<div class="columns">
    @foreach ($columns as $column)
        <x-builder.column
            :uid="$column['id']"
            :components="$column['components']"
        />
    @endforeach
</div>