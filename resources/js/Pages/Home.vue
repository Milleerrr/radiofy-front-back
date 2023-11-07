<script setup>
import Cards from '@/Widgets/HomeCards.vue';
import MainLayout from '@/Layouts/MainLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

</script>

<template>

    <Head title="Radiofy Home" />

    <MainLayout>
        <div class="container">
            <div class="row mb-5">
                <div class="col" id="text">
                    <h1 id="banner">Radiofy your music</h1>
                    <Link as="button" class="btn btn-secondary btn-lg mt-5" type="button" :href="route('search')">Search playslist</Link>
                </div>
                <div class="col">
                    <img id="banner-img" src="/assets/Soundwave.png">
                </div>
            </div>
        </div>
        <hr>

        <div class="container mt-5 text-center">
            <h1>What we do</h1>
            <div class="row d-flex">
                <div class="col">
                    <Cards />
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 offset-md-4">
                    <button class="btn btn-secondary btn-lg mt-5 mb-5" type="button">Learn more</button>
                </div>
            </div>
        </div>

        <div class="container bg-info-subtle my-5 py-2">
            <div class="row mb-5">
                <div class="col">
                    <img id="person" src="/assets/person.png">
                </div>
                <div class="col">
                    <h2 class="mb-3" id="banner-three">Redefining your listening experience</h2>
                    <p class="fs-6"> "I never miss a song using Radiofy! I used to always forget what song was played on the
                        radio, spending a
                        liftime endlessly searching for it. Now Radiofy remembers for me, just search the playlist I was
                        listening too and in one click it is saved forever to my Spotify account."</p>
                    <button id="learn-more" class="btn btn-secondary btn-lg mt-5 mb-5" type="button">Learn more</button>
                </div>
            </div>
        </div>
    </MainLayout>
</template>

<style scoped>
#text {
    margin-left: 5rem;
}

#banner {
    font-size: 10rem;
    font-weight: 300;
    line-height: 9rem;
    word-spacing: 21rem;

}

#what-we-do {
    font-size: 5rem;
    font-weight: 300;
}

#banner-img {
    width: 100%;
    height: auto;
}

#person {
    width: 80%;
    height: auto;
    position: relative;
    top: 4rem;
    right: 4.5rem;
}

#banner-three {
    font-size: 2.5rem;
    font-weight: 400;
}
</style>
