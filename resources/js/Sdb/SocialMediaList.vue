<template>
    <a
        v-if="$page.props.socialstream.providers.includes('google')"
        :href="route('oauth.redirect', 'google')"
        class="box"
        >
        <i class="fab fa-google"></i> Continue with <b>Google</b>
    </a>
    <a
        v-if="$page.props.socialstream.providers.includes('facebook')"
        :href="route('oauth.redirect', 'facebook')"
        class="box">
        <i class="fab fa-facebook"></i> Continue with <b>Facebook</b>
    </a>
    <a
        v-if="$page.props.socialstream.providers.includes('twitter')"
        :href="route('oauth.redirect', 'twitter')"
        class="box">
        <i class="fab fa-twitter"></i> Continue with <b>Twitter</b>
    </a>
</template>
