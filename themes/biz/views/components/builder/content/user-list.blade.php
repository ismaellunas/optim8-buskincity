<user-list
    @class($uniqueClass)
    :default-countries="{{ Illuminate\Support\Js::from($defaultCountries) }}"
    :default-types="{{ Illuminate\Support\Js::from($defaultTypes) }}"
    :country-options="{{ Illuminate\Support\Js::from($countryOptions) }}"
    :order-by-options="{{ Illuminate\Support\Js::from($orderByOptions) }}"
    :type-options="{{ Illuminate\Support\Js::from($typeOptions) }}"
    default-order-by="{{ $defaultOrderBy }}"
    excluded-id="{{ $encryptedExcludedId }}"
    roles="{{ $roles }}"
    url="{{ $url }}"
>
    <template v-slot="{ user }">
        <div class="column is-3">
            <a
                :href="user.profile_page_url ?? `#`"
                class="b752-profile-box box is-shadowless has-text-centered"
            >
                <figure class="b752-profile-picture image is-128x128 is-inline-block">
                    <img
                        class="is-rounded"
                        :src="user.profile_photo_url"
                        :alt="user.full_name"
                    >
                    <span
                        v-if="user.country"
                        class="flag"
                    >
                        <img
                            :src="`/images/flags/` + user.country.toLowerCase() + `.svg`"
                            alt="country"
                            class="is-rounded"
                        >
                    </span>
                </figure>

                <h2 class="title is-5 mt-4 mb-2">
                    @{{ user.stage_name ?? user.full_name }}
                </h2>
                <div class="content is-size-7">
                    <p>@{{ user.discipline ?? '&nbsp;' }}</p>
                </div>
            </a>
        </div>
    </template>
</user-list>
