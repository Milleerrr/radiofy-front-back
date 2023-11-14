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
let isLoading = ref(false);
let isSaving = ref(false)
let giphyImage = ref('');

const failAlert = () => {
    return Swal.fire(
        'Error!',
        'There was a problem adding to Spotify. Playlist name or Songs list must not be empty.',
        'error',
    );
}

const successAlert = () => {
    return Swal.fire(
        'Success!',
        'The playlist has magically been added to your Spotify account.', // You can use response.data.message if it contains the message
        'success',
    )
};

const scrollToBottom = () => {
    window.scrollTo({
        top: document.body.scrollHeight,
        behavior: 'smooth',
    });
};

const retrieveSongInfo = async () => {

    checkPlaylistNameIsNotEmpty();

    try {
        // Fetch playlist from JSON file
        let data = await fetchPlaylist();

        getRandomGif();
        isLoading.value = true;
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

        isLoading.value = false;
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

async function getRandomGif() {
    try {
        const response = await axios.get('/api/random-gif'); // Adjust the URL based on your actual API endpoint
        giphyImage.value = response.data.data.images.original.url;
    } catch (error) {
        console.error('Error fetching a random GIF:', error.response.data);
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

    if (songs.value.length === 0) return failAlert();
    // Filter the songs that are checked
    const tracksToAdd = songs.value.filter(song => song.checked);
    try {

        checkPlaylistNameIsNotEmpty();
        getRandomGif();
        
        isSaving.value = true;
        const response = await axios.post('api/spotify/add-to-spotify', {
            playlistName: playlistName.value,
            tracks: tracksToAdd,
        });

        isSaving.value = false;
        // Use SweetAlert to show a success message
        successAlert();

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
            <div class="input-group input-group-lg">
                <input v-model="playlistName" type="text" class="form-control" placeholder="Name your playlist"
                    aria-describedby="inputGroup-sizing-lg">
            </div>
            <div class="col-lg-3 offset-5 mt-3">
                <button class="btn btn-outline-success px-5">Search</button>
            </div>
        </form>
        

        <div v-if="isLoading">
            <button class="btn btn-primary btn-lg status-loading" type="button" disabled>
                <span class="spinner-grow spinner-grow-sm" aria-hidden="true"></span>
                <span role="status">Loading...</span>
            </button>

            <div class="mt-5 giphy-image">
                <img :src="giphyImage" />
            </div>
        </div>

        <div v-else>
            <button id="scroll-to-bottom-btn" class="btn btn-primary btn-lg" @click="scrollToBottom">
                ↓
            </button>

            <div class="btn-group" role="group" aria-label="Basic radio toggle button group">
                <input type="radio" class="btn-check" name="btnradio" id="btnradio1" autocomplete="off"
                    :checked="!isCheckAll" @change="checkAll">
                <label class="btn btn-outline-primary" for="btnradio1">Deselect all</label>

                <input type="radio" class="btn-check" name="btnradio" id="btnradio2" autocomplete="off"
                    :checked="isCheckAll" @change="checkAll">
                <label class="btn btn-outline-primary" for="btnradio2">Select all </label>
            </div>

            
            <SearchPlaylistCards v-for="song in songs" :key="song.id" :title="song.title" :artists="song.artist"
                :imageUrl="song.imageUrl" :audioUrl="song.previewUrl" :checked="song.checked"
                @update:checked="updateCheckedState(song, $event)" />

            <div class="row">
                <div class="col-lg-3 offset-5 mt-3">
                    <button id="add-to-spotify" class="btn btn-secondary btn-lg mt-5" @click="addToSpotify">Add to
                        Spotify</button>
                </div>
            </div>

            <div v-if="isSaving">
                <button class="btn btn-primary btn-lg status-saving" type="button" disabled>
                    <span class="spinner-grow spinner-grow-sm" aria-hidden="true"></span>
                    <span role="status">Saving...</span>
                </button>

                <div class="mt-5 giphy-image">
                    <img :src="giphyImage" />
                </div>˝
            </div>
        </div>

    </MainLayout>
</template>


<style scoped>
#scroll-to-bottom-btn {
    position: fixed;
    top: 50%;
    right: 20px;
    transform: translateY(-50%);
    z-index: 1000;
    cursor: pointer;
}

#add-to-spotify {
    background-color: rgb(5, 180, 34);
    border-color: rgb(5, 180, 34);
    position: relative;
    right: 0.9rem;
}

#add-to-spotify:hover {
    opacity: 70%;
}

.status-loading {
    position: relative;
    left: 42.25%;
    margin-top: 2rem;
}

.status-saving {
    position: relative;
    left: 43.25%;
    margin-top: 2rem;
}

.giphy-image {
    display: flex;
    justify-content: center;
}
</style>
