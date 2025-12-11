const { City, Country } = require('country-state-city');
const fs = require('fs');
const path = require('path');

const targetCountries = [
    'Albania', 'Andorra', 'Armenia', 'Austria', 'Azerbaijan', 'Belarus', 'Belgium',
    'Bosnia and Herzegovina', 'Bulgaria', 'Croatia', 'Cyprus', 'Czech Republic', 'Denmark',
    'Estonia', 'Finland', 'France', 'Georgia', 'Germany', 'Greece', 'Hungary', 'Iceland',
    'Ireland', 'Italy', 'Kazakhstan', 'Latvia', 'Liechtenstein', 'Lithuania',
    'Luxembourg', 'Malta', 'Moldova', 'Monaco', 'Montenegro', 'Netherlands', 'North Macedonia',
    'Norway', 'Poland', 'Portugal', 'Romania', 'Russia', 'San Marino', 'Serbia', 'Slovakia',
    'Slovenia', 'Spain', 'Sweden', 'Switzerland', 'Turkey', 'Ukraine', 'United Kingdom',
    'Vatican City'
];

// Manual mapping for some countries that might have different names in the library
const countryMapping = {
    'Czechia': 'Czech Republic',
    'North Macedonia': 'Macedonia',
    'Vatican City': 'Vatican City State (Holy See)',
    'Kosovo': 'Kosovo' 
};

const allCountries = Country.getAllCountries();
const citiesData = [];

targetCountries.forEach(inputName => {
    let searchName = countryMapping[inputName] || inputName;
    
    // Try exact match first
    let country = allCountries.find(c => c.name === searchName);
    
    // Try partial match if not found
    if (!country) {
        country = allCountries.find(c => c.name.includes(searchName));
    }

    if (country) {
        console.log(`Processing ${inputName} -> ${country.name} (${country.isoCode})...`);
        const cities = City.getCitiesOfCountry(country.isoCode);
        cities.forEach(city => {
            citiesData.push({
                name: city.name,
                country_code: city.countryCode,
                latitude: city.latitude,
                longitude: city.longitude,
                state_code: city.stateCode
            });
        });
    } else {
        console.warn(`WARNING: Country not found: ${inputName}`);
    }
});

const outputPath = path.join(__dirname, '../database/seeders/data/cities.json');
fs.writeFileSync(outputPath, JSON.stringify(citiesData, null, 2));

console.log(`Generated ${citiesData.length} cities in ${outputPath}`);
