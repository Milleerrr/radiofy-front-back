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
    if (!playlistName.value) {
        alert('Please enter a playlist name.');
        return;
    }

    try {
        const tokenResponse = await axios.post('api/spotify/get-token');
        const accessToken = tokenResponse.data.access_token;
        console.log('Spotify Access Token:', accessToken);

        const playlistResponse = await axios.post('api/spotify/create-playlist', {
            name: playlistName.value
        });
        const playlistId = playlistResponse.data.playlist_name;
        console.log('Created Playlist Id:', playlistId);

        let trackUris = [];
        for (const song of songs.value) {
            const trackResponse = await searchTrackOnSpotify(song.artist, song.title);
            if (trackResponse && trackResponse.data && trackResponse.data.uri) {
                trackUris.push(trackResponse.data.uri);
            }
        }

        if (trackUris.length > 0) {
            await addTracksToPlaylistOnSpotify(playlistId, trackUris);
        }

        // Handle success, maybe redirect to the Spotify playlist or show a success message
    } catch (error) {
        console.error('Error in playlist creation or track search:', error.response.data);
        // Handle errors, maybe show an error message to the user
    }
};

const addTracksToPlaylistOnSpotify = async (playlistId, trackUris) => {
    try {
        await axios.post('api/spotify/add-tracks', {
            playlist_id: playlistId,
            track_uris: trackUris
        });
        console.log('Tracks added to the playlist successfully');
    } catch (error) {
        console.error('Error adding tracks to the playlist:', error.response.data);
        // Handle errors, maybe show an error message to the user
    }
};


const searchTrackOnSpotify = async (artist, trackTitle) => {
    try {
        const trackResponse = await axios.post('api/spotify/search-track', {
            artist: artist,
            trackTitle: trackTitle
        });

        console.log('Search Results:', trackResponse.data.uri);
        return trackResponse; // Return the response so the URI can be used
    } catch (error) {
        console.error('Error in searching track:', error.response.data);
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
            <div class="col-md-3 offset-md-5">
                <button id="add-to-spotify" class="btn btn-secondary btn-lg mt-5" @click="createPlaylistOnSpotify">Add to Spotify</button>
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
