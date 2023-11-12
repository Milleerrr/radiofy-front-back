<script setup>
import { ref, watchEffect } from 'vue';
import SearchPlaylistCards from '@/Widgets/SearchPlaylistCards.vue';
import MainLayout from '@/Layouts/MainLayout.vue';
import axios from 'axios';
import { Head } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

// Variables
// Reactive properties for the playlist and selection states
let selectedStation = ref('');
let songs = ref([]);
let playlistName = ref('');
let isCheckAll = ref(false);

const failAlert = () => {
    return Swal.fire (
            'Error!',
            'There was a problem adding to Spotify. Check playlist name is not empty',
            'error',
        );
}

const retrieveSongInfo = async () => {

    checkPlaylistNameIsNotEmpty();

    try {
        // Fetch playlist from JSON file

        let data = await fetchPlaylist();

        // Loop through each track and send individual requests
        const trackDetails = await Promise.all(data.Radio1Dance.map(async (song) => {
            try {
                const spotifyResponse = await axios.post('/api/spotify/retrieve-song-info', {
                    artist: song.artist,
                    trackTitle: song.title,
                });

                return {
                    ...song,
                    checked: true,
                    imageUrl: spotifyResponse.data.album.images[0].url,
                    artists: spotifyResponse.data.artists.map(artist => artist.name),
                    title: spotifyResponse.data.name,
                    previewUrl: spotifyResponse.data.preview_url,
                };
            } catch (error) {
                console.error('Error retrieving song info:', error.response.data);
                return song; // Return the original song if the API call fails
            }
        }));

        // Update the songs array with the returned objects for each song
        songs.value = trackDetails;

    } catch (error) {
        // Check if the error comes from axios or fetch and handle accordingly
        if (error.response) { // This is an axios error
            console.error('Axios error:', error.response.data);
        } else { // This is a fetch error
            console.error('Fetch error:', error.message);
        }
    }
};

const fetchPlaylist = async () => {
    const response = await fetch(`./data/${selectedStation.value}.json`);
    if (!response.ok) {
        throw new Error('Network response was not ok for fetching playlist data.');
    }
    return response.json();
}


const updateCheckedState = (song, isChecked) => {
    // Find the song in the songs array and update its checked property
    const songToUpdate = songs.value.find(s => s.id === song.id);
    if (songToUpdate) {
        songToUpdate.checked = isChecked;
    }
};

const checkPlaylistNameIsNotEmpty = () => {
    if (!playlistName.value) {
        return failAlert();
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
    // Filter the songs that are checked
    const tracksToAdd = songs.value.filter(song => song.checked);
    try {
        checkPlaylistNameIsNotEmpty();
        const response = await axios.post('api/spotify/add-to-spotify', {
            playlistName: playlistName.value,
            tracks: tracksToAdd,
        });

        // Use SweetAlert to show a success message
        Swal.fire(
            'Success!',
            'The playlist has magically been added to your Spotify account.', // You can use response.data.message if it contains the message
            'success',
        );

        console.log(response.data.message); // Assuming the backend sends back a success message
        // Handle success here
    } catch (error) {
        console.error('Error adding to Spotify:', error.response.data);

        // Use SweetAlert to show an error message
        failAlert();
    }
};

</script>


<template>
    <Head title="Search" />

    <MainLayout>
        <div class="container">
            <form @submit.prevent="retrieveSongInfo">
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
            <input type="radio" class="btn-check" name="btnradio" id="btnradio1" autocomplete="off" :checked="!isCheckAll"
                @change="checkAll">
            <label class="btn btn-outline-primary" for="btnradio1">Deselect all</label>

            <input type="radio" class="btn-check" name="btnradio" id="btnradio2" autocomplete="off" :checked="isCheckAll"
                @change="checkAll">
            <label class="btn btn-outline-primary" for="btnradio2">Select all </label>
        </div>

        <div class="container">
            <SearchPlaylistCards v-for="song in songs" :key="song.id" :title="song.title" :artists="song.artist"
                :imageUrl="song.imageUrl" :audioUrl="song.previewUrl" :checked="song.checked"
                @update:checked="updateCheckedState(song, $event)" />
        </div>

        <div class="row">
            <div class="col-lg-3 offset-5 mt-3" id="">
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
    position: relative;
    right: 1rem;
}

#add-to-spotify:hover {
    opacity: 70%;
}
</style>
