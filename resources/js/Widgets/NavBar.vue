<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <nav class="navbar navbar-expand-lg bg-body-tertiary container-fluid mt-4 mb-5">
        <div class="container-fluid">
            <Link :href="route('/')" class="navbar-brand">
            <img src="/assets/radiofy.svg"> Radiofy
            </Link>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
                aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <Link class="nav-link hover" aria-current="page" :href="route('search')">Search playlist</Link>
                    </li>
                </ul>
                <div class="d-flex">
                    <div v-if="!$page.props.auth.user">
                        <Link :href="route('login')"><button type="button"
                            class="btn btn-primary btn-sm" id="sign-up">Sign up</button></Link>
                        <Link type="button" :href="route('login')"><button type="button"
                            class="btn btn-secondary btn-sm">Log in</button></Link>
                    </div>
                    <div v-else>
                        <span>Welcome, <strong>{{ $page.props.auth.user.name }}</strong>!</span>
                        <Link :href="route('logout')"><button type="button"
                            class="btn btn-outline-danger btn-sm ms-3">Log out</button></Link>
                        <!-- <a class="nav-link m-1" herf="#">
                            <svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 448 512">
                                <path
                                    d="M224 256A128 128 0 1 0 224 0a128 128 0 1 0 0 256zm-45.7 48C79.8 304 0 383.8 0 482.3C0 498.7 13.3 512 29.7 512H418.3c16.4 0 29.7-13.3 29.7-29.7C448 383.8 368.2 304 269.7 304H178.3z" />
                            </svg>
                        </a> -->
                    </div>
                </div>
            </div>
        </div>
    </nav>
</template>

<style scoped>
#sign-up {
    background-color: rgb(5, 180, 34);
    border-color: rgb(5, 180, 34);
    margin-right: 10px;
}

#sign-up:hover {
    opacity: 70%;
}</style>
