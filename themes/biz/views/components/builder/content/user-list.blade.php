<user-list
    :countries="{{ Illuminate\Support\Js::from($countries) }}"
    :country-options="{{ Illuminate\Support\Js::from($countryOptions) }}"
    :order-by-options="{{ Illuminate\Support\Js::from($orderByOptions) }}"
    default-order-by="{{ $defaultOrderBy }}"
    excluded-id="{{ $encryptedExcludedId }}"
    roles="{{ $roles }}"
    url="{{ $url }}"
>
    <template v-slot="{ user }">
        <div class="column is-3">
            <div class="card">
                <div class="card-content">
                    <div class="media">
                        <figure class="image is-128x128">
                            <img
                                class="is-rounded"
                                :src="user.profile_photo_url"
                                :alt="user.full_name"
                            >
                        </figure>
                    </div>

                    <div class="content">
                        <p class="title is-4">
                            <a :href="user.profile_page_url">
                                @{{ user.stage_name ?? '&nbsp;' }}
                            </a>
                        </p>
                        <p class="subtitle is-6">
                            <a :href="user.profile_page_url">
                                @{{ user.full_name }}
                            </a>
                        </p>
                        <p class="subtitle is-6">@{{ user.country ?? '&nbsp;' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </template>
</user-list>
