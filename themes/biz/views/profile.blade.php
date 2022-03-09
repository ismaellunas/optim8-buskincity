<x-layouts.master>
    <x-slot name="title">
        Profile
    </x-slot>

    <section
        id="about"
        class="section theme-font"
    >
        <!-- Title -->
        <div class="section-heading has-text-centered">
            <h3 class="title is-2">About Me</h3>
            <h4 class="subtitle is-5">Jack of all trades, master of "some"</h4>
            <div class="container">
                <p>Web developer with more than <strong>4 years</strong> of well-rounded experience with a degree in the
                    field of
                    <strong>Computer Science</strong>, extensive knowledge of modern Web techniques and love for <strong>Coffee</strong>.
                    Looking for an opportunity to work and upgrade, as well as being involved in an organization that
                    believes
                    in gaining a competitive edge and giving back to the community.
                </p>
            </div>
        </div>

        <div class="columns has-same-height is-gapless mt-4">
            <div class="column">
                <!-- Profile picture -->
                <div class="card mr-2">
                    <div class="card-image">
                        <figure class="image is-4by3">
                            <img src="https://source.unsplash.com/random/1280x960" alt="Placeholder image">
                        </figure>
                    </div>
                </div>
            </div>
            <div class="column">
                <!-- Profile -->
                <div class="card">
                    <div class="card-content">
                        <h3 class="title is-4">Profile</h3>

                        <div class="content">
                            <table class="table-profile">
                                <tbody>
                                    <tr>
                                        <th colspan="1" style="width: 30%"></th>
                                        <th colspan="2"></th>
                                    </tr>
                                    <tr>
                                        <td>Name:</td>
                                        <td>{{ $user->full_name }}</td>
                                    </tr>
                                    <tr>
                                        <td>Email:</td>
                                        <td>{{ $user->email }}</td>
                                    </tr>
                                    <tr>
                                        <td>Language:</td>
                                        <td>{{ $user->originLanguage ? $user->originLanguage->name : "-" }}</td>
                                    </tr>
                                </tbody>
                            </table>

                            @foreach ($fieldGroups as $key => $fields)
                                <x-form.fields.field
                                    :title="$key"
                                    :fields="$fields"
                                    :user-locale="$user->origin_language_code"
                                />
                            @endforeach
                        </div>
                        <br>
                        <div class="buttons has-addons is-centered">
                            <a href="#" class="button is-link">LinkedIn</a>
                            <a href="#" class="button is-link">Twitter</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-layouts.master>