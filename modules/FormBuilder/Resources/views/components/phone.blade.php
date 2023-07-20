@props([
    'field' => [],
    'fieldName' => 'name',
    'dropdownId' => uniqid(),
])
{{--
    :label="schema.label"
    :maxlength="schema.maxlength"
    :placeholder="schema.placeholder"
    :disabled="schema.is_disabled"
    :readonly="schema.is_readonly"
    :required="schema.is_required"
    :message="message"
    :country-options="schema.countryOptions"
    :default-country="schema.defaultCountry"
--}}

{{--
<x-form.phone
    v-model="form.{{ $fieldName }}"
    :label="$field['label']"
>
    <x-slot:notes>
        <div>
            <ul class="help ml-0 is-info" style="list-style-type: none">
            @foreach ($field['notes'] as $note)
                <li>{{ $note }}</li>
            @endforeach
            </ul>
        </div>

        <div v-show="getError('{{ $fieldName }}', null, formErrors)">
            <p
                v-for="(msg, index) in getError('{{ $fieldName }}', null, formErrors)"
                :key="index"
                class="help is-danger"
            >
                @{{ msg }}
            </p>
        </div>
    </x-slot>
</x-form>
--}}

<x-field>
    <x-label :label="$field['label']" :required="$field['is_required']" />

    <form-phone
        v-model="form.{{ $fieldName }}"
        default-country="{{ $field['defaultCountry'] }}"
        :country-options="{{ Illuminate\Support\Js::from($field['countryOptions']) }}"
        v-slot="{ computedValue, countries, countryId, dial, isActive, setRefDropdownMenu, setRefTrigger, term, handlers }"
    >
        <div class="field has-addons mb-0">
            <div class="control">
                <div
                    class="dropdown"
                    :class="{'is-active': isActive}"
                >
                    <div
                        :ref="(el) => setRefTrigger(el)"
                        class="dropdown-trigger"
                        @if ($field['is_required'] || !($field['is_disabled'] || $field['is_readonly'])) @click="handlers.toggleMenu()" @endif
                    >
                        <button
                            class="button"
                            type="button"
                            aria-haspopup="true"
                            aria-controls="dropdown-{{ $dropdownId }}"
                            @disabled($field['is_disabled'])
                            @readonly($field['is_readonly'])
                        >
                            <span>@{{ countryId }}</span>
                            <x-icon icon="fa-angle-down" is-small />
                        </button>
                    </div>
                    <div
                        :ref="(el) => setRefDropdownMenu(el)"
                        class="dropdown-menu pt-0"
                        id="dropdown-{{ $dropdownId }}"
                        role="menu"
                    >
                        <div class="dropdown-content">
                            <div class="field has-addons">
                                <div class="control has-icons-left is-expanded">
                                    <input
                                        class="input"
                                        type="text"
                                        placeholder="{{ __('Search') }} ..."
                                        :value="term"
                                        @keyup.prevent="handlers.search($event.target.value)"
                                        @keydown.enter.prevent
                                    />
                                    <x-icon icon="fa-search" is-small class="is-left" />
                                </div>
                                <div class="control">
                                    <a
                                        class="button is-small"
                                        @click.prevent="handlers.clearTermOrClose"
                                    >
                                        <x-icon icon="fa-times" is-small />
                                    </a>
                                </div>
                            </div>

                            <div
                                style="max-height: 200px; overflow-x: auto;"
                                @scroll="handlers.onScroll"
                            >
                                <a
                                    v-for="country in countries"
                                    :key="country.id"
                                    href="#"
                                    class="dropdown-item"
                                    :class="{'is-active': country.id == countryId}"
                                    @click.prevent="handlers.selectCountry(country)"
                                >
                                    @{{ country.value }} (+@{{ country.dial }})
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="control has-icons-left is-expanded">
                <input
                    v-model="computedValue['number']"
                    class="input"
                    type="text"
                    placeholder="{{ $field['placeholder'] ?? '' }}"
                    maxlength="24"
                    @disabled(!$field['is_required'] && $field['is_disabled'])
                    @readonly(!$field['is_required'] && $field['is_readonly'])
                    @keypress="handlers.numberOnly"
                >
                <span class="icon is-left">+@{{ dial }}</span>
            </div>
        </div>
    </form-phone>

    @if ($field['notes'])
        <ul class="help ml-0 is-info" style="list-style-type: none">
        @foreach ($field['notes'] as $note)
            <li>{{ $note }}</li>
        @endforeach
        </ul>
    @endif

    <div v-show="getError('{{ $fieldName }}.number', null, formErrors)">
        <p
            v-for="(msg, index) in getError('{{ $fieldName }}.number', null, formErrors)"
            :key="index"
            class="help is-danger"
        >
            @{{ msg }}
        </p>
    </div>
</x-field>

{{--
<form-phone
    v-model="form.{{ $fieldName }}"
    :errors="formErrors"
    :schema="{{ Illuminate\Support\Js::from($field) }}"
>
    <template #note>
        <div>
            <ul class="help ml-0 is-info" style="list-style-type: none">
            @foreach ($field['notes'] as $note)
                <li>{{ $note }}</li>
            @endforeach
            </ul>
        </div>
    </template>
</form-phone>
--}}
