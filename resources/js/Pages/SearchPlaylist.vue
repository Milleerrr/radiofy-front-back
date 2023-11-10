<script setup>
import { ref, watchEffect } from 'vue';
import SearchPlaylistCards from '@/Widgets/SearchPlaylistCards.vue';
import MainLayout from '@/Layouts/MainLayout.vue';
import axios from 'axios';
import { Head } from '@inertiajs/vue3';

// Variables
// Reactive properties for the playlist and selection states
let selectedStation = ref('');
let songs = ref([]);
let playlistName = ref('');
let isCheckAll = ref(false);

const fetchPlaylist = async () => {
    try {
        const response = await fetch(`./data/${selectedStation.value}.json`);
        if (!response.ok) {
            throw new Error('Network response was not ok.');
        }
        const data = await response.json();
        // Add 'checked' property to each song object
        songs.value = data.Radio1Dance.map(song => ({ ...song, checked: true }));
    } catch (error) {
        console.error('Fetch error:', error.response.data);
    }
};

const updateCheckedState = (song, isChecked) => {
    // Find the song in the songs array and update its checked property
    const songToUpdate = songs.value.find(s => s.id === song.id);
    if (songToUpdate) {
        songToUpdate.checked = isChecked;
    }
};

const checkPlaylistNameIsNotEmpty = () => {
    if (!playlistName.value) {
        alert('Please enter a playlist name.');
        return;
    }
}

// Function to toggle select all/deselect all
const checkAll = () => {
    isCheckAll.value = !isCheckAll.value;
    songs.value.forEach((song) => {
        song.checked = isCheckAll.value;
    });
};

// Function to update the check all state based on individual song selections
const updateCheckall = () => {
    isCheckAll.value = songs.value.every(song => song.checked);
};

// Watch effect to update 'isCheckAll' when 'songs' change
watchEffect(() => {
    updateCheckall();
});

const addToSpotify = async () => {
    checkPlaylistNameIsNotEmpty();

    // Filter the songs that are checked
    const tracksToAdd = songs.value.filter(song => song.checked);
    try {
        const response = await axios.post('api/spotify/add-to-spotify', {
            playlistName: playlistName.value,
            tracks: tracksToAdd,
        });
        alert('Hooray! Playlist created');
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


        <div class="btn-group" role="group" aria-label="Basic radio toggle button group">
            <input type="radio" class="btn-check" name="btnradio" id="btnradio1" autocomplete="off" :checked="!isCheckAll" @change="checkAll">
            <label class="btn btn-outline-primary" for="btnradio1">Deselect all</label>

            <input type="radio" class="btn-check" name="btnradio" id="btnradio2" autocomplete="off" :checked="isCheckAll" @change="checkAll">
            <label class="btn btn-outline-primary" for="btnradio2">Select all </label>
        </div>

        <div class="container">
            <SearchPlaylistCards v-for="song in songs" :key="song.id" :title="song.title" :artist="song.artist"
                :checked="song.checked" @update:checked="updateCheckedState(song, $event)" />
        </div>

        <div class="row">
            <div class="col-md-3 offset-lg-5">
                <button id="add-to-spotify" class="btn btn-secondary btn-lg mt-5" @click="addToSpotify">Add to
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
