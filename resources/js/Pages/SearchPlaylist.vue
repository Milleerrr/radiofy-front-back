<script setup>
import { ref, watchEffect } from 'vue';
import MainLayout from '@/Layouts/MainLayout.vue';
import SearchPlaylistCards from '@/Widgets/SearchPlaylistCards.vue';
import BBCProgrammeCards from '@/Widgets/BBCProgrammeCards.vue';
import axios from 'axios';
import { Head } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

// Variables
// Reactive properties for the playlist and selection states
let selectedStation = ref('');
let date = ref('');
let songs = ref([]);
let playlistName = ref('');
let isCheckAll = ref(false);
let isLoading = ref(false);
let isSaving = ref(false)
let giphyImage = ref('');
let programmeList = ref([]);
let selectedProgramme = ref();
let scrapedSongs = ref([]);
let schedule = ref(false);
let songList = ref(false);

// Sweet alerts for success and fail 
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

// Scroll to bottom of the page
const scrollToBottom = () => {
    window.scrollTo({
        top: document.body.scrollHeight,
        behavior: 'smooth',
    });
};

// Returns a random 'waiting' gif from giphy whilst the songs are being searched
async function getRandomGif() {
    try {
        const response = await axios.get('/api/random-gif'); // Adjust the URL based on your actual API endpoint
        giphyImage.value = response.data.data.images.original.url;
    } catch (error) {
        console.error('Error fetching a random GIF:', error.response.data);
    }
};

// Returns selected radio station programme list, user can then select from the list 
// which programme they want to save to their Spotify account
// Inside your Vue component

const getSchedule = async () => {
    schedule.value = false;
    songList.value = false;
    try {
        const response = await axios.get(route('api.schedule.index'), {
            params: {
                station: selectedStation.value,
                date: date.value
            }
        });
        // Since the backend now always returns an array of programmes, 
        // we assign it directly to programmeList.value

        programmeList.value = response.data.programme_list;

        schedule.value = true; // Set to false after loading is complete
    } catch (error) {
        console.error('Error fetching schedule:', error.response.data);
        schedule.value = false; // Ensure to set loading to false even if there's an error
    }
};



// Binds to each BBCProgrammeCards component. When clicked, it will save the details of the 
// card to a variable called selectedProgramme. This is then used later for scraping the songs
const setSelectedProgramme = (programme) => {
    selectedProgramme.value = programme;
};


// Srcape the songs of the selected programme and save them to scrapedSongs
// const scrapeSongsFromProgramme = async () => {
//     if (!selectedProgramme.value) {
//         console.error('No programme selected');
//         return;
//     }

//     try {
//         // Assuming '/api/scrape-songs' is your endpoint for scraping songs from a given URL
//         const response = await axios.post('/scrape-songs', {
//             link: selectedProgramme.value.link
//         });

//         scrapedSongs.value = response.data.scraped_songs;
//     } catch (error) {
//         console.error('Error scraping songs:', error.response.data);
//     }
// };

// When the user clicks the search button, call scrapeSongsFromProgramme to scrape the songs.
// Then search the songs on Spotify to retrieve the songs objects
const searchSongs = async () => {
    if (selectedProgramme.value) {
        // Hide schedule
        schedule.value = false;
        // Show song list
        songList.value = true;

        try {
            const playlistId = extractProgrammeId(selectedProgramme.value.link);
            const response = await axios.get('/api/schedule/programme/details', {
                params: { playlist_id: playlistId }
            });
            songs.value = response.data; // Assuming the response data is structured correctly
        } catch (error) {
            console.error('Error fetching songs and artists:', error.response.data);
        }
    } else {
        console.error('No programme selected');
    }
};

// Helper function to extract programme ID from the URL
function extractProgrammeId(link) {
    const parts = link.split('/');
    return parts[parts.length - 1];
}

// const retrieveSongInfo = async () => {

//     try {
//         getRandomGif();
//         isLoading.value = true;

//         // Make a single request with all songs
//         const spotifyResponse = await axios.post('/api/spotify/retrieve-song-info', {
//             songs: scrapedSongs.value.map(song => ({
//                 artist: song.artist,
//                 trackTitle: song.title,
//             })),
//         });

//         // Assume spotifyResponse.data is an array of song details corresponding to each song
//         const trackDetails = spotifyResponse.data.map((songDetails, index) => ({
//             ...scrapedSongs.value[index],
//             checked: true,
//             imageUrl: songDetails.album.images[0].url,
//             artists: songDetails.artists.map(artist => artist.name),
//             title: songDetails.name,
//             previewUrl: songDetails.preview_url,
//             spotifyUri: songDetails.uri,
//         }));

//         // Update the songs array with the returned objects for each song
//         songs.value = trackDetails;

//         isLoading.value = false;
//     } catch (error) {
//         // Handle errors
//         isLoading.value = false;
//         console.error('Error retrieving song info:', error.response.data);
//     }
// };


// Adds the selected songs the ther users Spotify account. It takes the playlist name
// songs array and passes that to Spotify to create a playlist and populate it with songs
const addToSpotify = async () => {

    if (songs.value.length === 0) return failAlert();
    // Filter the songs that are checked
    const tracksToAdd = songs.value.filter(song => song.checked);

    try {

        getRandomGif();

        isSaving.value = true;

        await axios.post('api/spotify/add-to-spotify', {
            playlistName: playlistName.value,
            tracks: tracksToAdd,
        });

        isSaving.value = false;
        // Use SweetAlert to show a success message
        successAlert();

    } catch (error) {
        console.error('Error adding to Spotify:', error.response.data);
        isSaving.value = false;
        // Use SweetAlert to show an error message
        failAlert();
    }
};

// Updates the checked property of each song when clicked on
const updateCheckedState = (song, isChecked) => {
    // Find the song in the songs array and update its checked property
    const songToUpdate = songs.value.find(s => s.id === song.id);
    if (songToUpdate) {
        songToUpdate.checked = isChecked;
    }
};

// Checks if playlist name is empty
const checkPlaylistNameIsNotEmpty = () => {
    if (!playlistName.value) {
        return failAlert();
    }
}

// Function to toggle select all/deselect all songs an updates the checked property
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

</script>


<template>
    <Head title="Search" />

    <MainLayout>
        <div class="container mt-5">
            <div class="row justify-content-md-center">
                <div class="col-md-auto">
                    <form>

                        <!-- Radio Station Selector -->
                        <div class="mb-3">
                            <select v-model="selectedStation" class="form-select form-select-lg">
                                <option disabled selected>Select a Radio Station</option>
                                <option value="radio_one">Radio 1</option>
                                <option value="radio_one_dance">Radio 1 Dance</option>
                                <option value="radio_one_relax">Radio 1 Relax</option>
                                <option value="1xtra">Radio 1Xtra</option>
                                <option value="radio_two">Radio 2</option>
                                <option value="radio_three">Radio 3</option>
                            </select>
                        </div>

                        <!-- Date Picker -->
                        <div class="mb-3">
                            <input id="startDate" class="form-control form-control-lg" type="date" v-model="date" />
                        </div>

                        <!-- Playlist Name Input -->
                        <div class="mb-3">
                            <input v-model="playlistName" type="text" class="form-control form-control-lg"
                                placeholder="Name your playlist" aria-label="Playlist name">
                        </div>

                        <!-- Get Schedule Button -->
                        <div class="d-grid gap-2">
                            <button class="btn btn-success btn-lg" type="button" @click="getSchedule">Get Radio Station
                                Schedule</button>
                        </div>

                        <!-- Search Button -->
                        <div class="d-grid gap-2 mt-3">
                            <button class="btn btn-success btn-lg" type="button" @click="searchSongs">Search</button>
                        </div>

                    </form>
                </div>
            </div>
        </div>


        <div v-if="isLoading">
            <button class="btn btn-primary btn-lg status-loading" type="button" disabled>
                <span class="spinner-grow spinner-grow-sm me-2" aria-hidden="true"></span>
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

            <div v-if="songList" class="btn-group d-flex col-lg-3 col-md-4 mx-auto mt-3" role="group"
                aria-label="Basic radio toggle button group">
                <input type="radio" class="btn-check" name="btnradio" id="btnradio1" autocomplete="off"
                    :checked="!isCheckAll" @change="checkAll">
                <label class="btn btn-outline-primary" for="btnradio1">Deselect all</label>

                <input type="radio" class="btn-check" name="btnradio" id="btnradio2" autocomplete="off"
                    :checked="isCheckAll" @change="checkAll">
                <label class="btn btn-outline-primary" for="btnradio2">Select all </label>
            </div>

            <div v-if="songList">
                <SearchPlaylistCards v-for="song in songs" :key="song.id" :title="song.title" :artists="song.artist"
                    :imageUrl="song.imageUrl" :audioUrl="song.previewUrl" :checked="song.checked"
                    @update:checked="updateCheckedState(song, $event)" />
            </div>

            <div v-if="schedule" :class="{ 'has-selection': selectedProgramme }">
                <BBCProgrammeCards v-for="programme in programmeList" 
                    :key="programme.playlistDetails.link" 
                    :title="programme.playlistDetails.primary_title"
                    :secondaryTitle="programme.playlistDetails.secondary_title" 
                    :synopsis="programme.playlistDetails.synopsis" 
                    :image="programme.playlistDetails.image_url" 
                    :isSelected="selectedProgramme === programme"
                    @checked="setSelectedProgramme(programme)" />
            </div>

            <div v-if="songList" class="d-flex justify-content-center mt-3">
                <button id="add-to-spotify" class="btn btn-secondary btn-lg mt-5" @click="addToSpotify">Add to
                    Spotify</button>
            </div>

            <div v-if="isSaving">
                <button class="btn btn-primary btn-lg status-saving" type="button" disabled>
                    <span class="spinner-grow spinner-grow-sm me-2" aria-hidden="true"></span>
                    <span role="status">Saving...</span>
                </button>

                <div class="mt-5 giphy-image">
                    <img :src="giphyImage" />
                </div>
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

.giphy-image,
#playlist-name {
    display: flex;
    justify-content: center;
}

.form-control {
    width: auto;
}

.programme-card {
    transition: opacity 0.3s ease;
    opacity: 1;
}

/* When a card is selected, only then apply the dimming effect to others */
.has-selection .programme-card:not(.selected) {
    opacity: 0.5;
    /* Dim other cards */
}

/* Highlight the selected card */
.programme-card.selected {
    background-color: #e9ecef;
    /* Highlight color for the selected card */
}
</style>
