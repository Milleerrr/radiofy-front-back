<script setup>
import { ref } from 'vue';
import SearchPlaylistCards from '@/Widgets/SearchPlaylistCards.vue';
import MainLayout from '@/Layouts/MainLayout.vue';
import { Head } from '@inertiajs/vue3';

// Variables
// Save selected station
let selectedStation = ref('');
// Save an array of songs with titles and artist/s
let songs = ref([]);

const fetchPlaylist = async () => {
    const response = await fetch(`./data/${selectedStation.value}.json`);
    const data = await response.json();
    songs.value = data.Radio1Dance;
}


</script>


<template>
    <Head title="Search" />

    <MainLayout>
        <div class="container">
            <form @submit.prevent="fetchPlaylist">
                <select v-model="selectedStation" class="form-select form-select-lg mb-3 text-center"
                    aria-label="Large select example">
                    <option disabled selected>Select a Radio Station</option>
                    <option value="1">Radio 1</option>
                    <option value="radio_1_dance">Radio 1 Dance</option>
                    <option value="3">Radio 1 Relax</option>
                    <option value="4">Radio 1Xtra</option>
                    <option value="5">Radio 2</option>
                    <option value="6">Radio 3</option>
                </select>
                <div class="container input-group input-group-lg">
                    <input type="text" class="form-control" aria-label="Sizing example input"
                        placeholder="Name your playlist" aria-describedby="inputGroup-sizing-lg">
                </div>
                <div class="col-lg-3 offset-5 mt-3">
                    <button class="btn btn-outline-success px-5">Search</button>
                </div>
            </form>
        </div>
        <ul>
            <li v-for="song in songs" :key="song.id">
                {{ song.title }} by {{ song.artist }}
            </li>
        </ul>
        <!-- <div class="container">
            <SearchPlaylistCards v-for="n in 5" :key="n" />
        </div> -->

        <div class="row">
            <div class="col-md-3 offset-md-5">
                <button id="add-to-spotify" class="btn btn-secondary btn-lg mt-5" src="#">Add to Spotify</button>
            </div>
        </div>
    </MainLayout>
</template>


<style scoped>
#add-to-spotify {
    background-color: rgb(5, 180, 34);
    border-color: rgb(5, 180, 34);
}

#add-to-spotify:hover {
    opacity: 70%;
}
</style>
