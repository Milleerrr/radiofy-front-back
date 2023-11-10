<script setup>
import { ref } from 'vue';
import SearchPlaylistCards from '@/Widgets/SearchPlaylistCards.vue';
import MainLayout from '@/Layouts/MainLayout.vue';
import axios from 'axios';
import { Head, usePage } from '@inertiajs/vue3';

// Variables
// Save selected station
let selectedStation = ref('');
// Save an array of songs with titles and artist/s
let songs = ref([]);
let playlistName = ref('');

const fetchPlaylist = async () => {
    const response = await fetch(`./data/${selectedStation.value}.json`);
    const data = await response.json();
    songs.value = data.Radio1Dance;
}

const addToSpotify = async () => {
    if (!playlistName.value) {
        alert('Please enter a playlist name.');
        return;
    }

    try {
        const response = await axios.post('api/spotify/add-to-spotify', {
            playlistName: playlistName.value,
            tracks: songs.value
        });
        alert('Hooray Playlist created');
        console.log(response.data.message); // Assuming the backend sends back a success message
        // Handle success here
    } catch (error) {
        console.error('Error adding to Spotify:', error.response.data);
        // Handle error here
    }
};

</script>


<template>
    <Head title="Search" />

    <MainLayout>
        <div class="container">
            <form @submit.prevent="fetchPlaylist">
                <select v-model="selectedStation" class="form-select form-select-lg mb-3 text-center"
                    aria-label="Large select example">
                    <option disabled selected>Select a Radio Station</option>
                    <option value="radio_1">Radio 1</option>
                    <option value="radio_1_dance">Radio 1 Dance</option>
                    <option value="radio_1_relax">Radio 1 Relax</option>
                    <option value="radio_1_xtra">Radio 1Xtra</option>
                    <option value="radio_2">Radio 2</option>
                    <option value="radio_3">Radio 3</option>
                </select>
                <div class="container input-group input-group-lg">
                    <input v-model="playlistName" type="text" class="form-control" placeholder="Name your playlist"
                        aria-describedby="inputGroup-sizing-lg">
                </div>
                <div class="col-lg-3 offset-5 mt-3">
                    <button class="btn btn-outline-success px-5">Search</button>
                </div>
            </form>
        </div>

        <div class="container">
            <SearchPlaylistCards v-for="song in songs" :key="song.id" :title="song.title" :artist="song.artist" />
        </div>

        <div class="row">
            <div class="col-md-3 offset-lg-5">
                <button id="add-to-spotify" class="btn btn-secondary btn-lg mt-5" @click="addToSpotify">Add to Spotify</button>
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
