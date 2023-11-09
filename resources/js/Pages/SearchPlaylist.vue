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
const props = usePage().props;


const fetchPlaylist = async () => {
    const response = await fetch(`./data/${selectedStation.value}.json`);
    const data = await response.json();
    songs.value = data.Radio1Dance;
}

const createPlaylistOnSpotify = async () => {
    if (!playlistName.value || !songs.value.length) {
        alert('Please enter a playlist name and select songs.');
        return;
    }

    try {
        const response = await axios.post(route('spotify.add-to-playlist'), {
            name: playlistName.value,
            songs: songs.value.map(song => ({ id: song.id, artist: song.artist, title: song.title }))
        });

        console.log(response.data);
        // Handle success, maybe redirect to the Spotify playlist or show a success message
    } catch (error) {
        console.error('Error adding playlist to Spotify:', error);
        // Handle errors, maybe show an error message to the user
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
                    <option value="1">Radio 1</option>
                    <option value="radio_1_dance">Radio 1 Dance</option>
                    <option value="3">Radio 1 Relax</option>
                    <option value="4">Radio 1Xtra</option>
                    <option value="5">Radio 2</option>
                    <option value="6">Radio 3</option>
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
            <div class="col-md-3 offset-md-5">
                <button id="add-to-spotify" class="btn btn-secondary btn-lg mt-5" @click="createPlaylistOnSpotify">Add to
                    Spotify</button>
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
