<script setup>
import { ref, watchEffect, computed } from 'vue';
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
let programmeList = ref([]);
let selectedProgramme = ref();
let schedule = ref(false);
let songList = ref(false);


const query = ref('');

const filteredSongs = computed(() => {

    if (query.value)
        return songs.value.filter(s => s.title.toLocaleLowerCase().indexOf(query.value.toLocaleLowerCase()) !== -1)
    return songs.value;
});

const maxDate = new Date().toISOString().split('T')[0]

// Sweet alerts for success and fail 
const failAlert = () => {
    return Swal.fire(
        'Error! 😭',
        'There was a problem adding to Spotify. Playlist name or Songs list must not be empty.',
        'error',
    );
}

const successAlert = () => {
    return Swal.fire(
        'Success! 😁',
        'The playlist has magically been added to your Spotify account.',
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


// Returns selected radio station programme list, user can then select from the list 
// which programme they want to save to their Spotify account

const getSchedule = async () => {
    schedule.value = false;
    songList.value = false;
    isLoading.value = true;
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
        isLoading.value = false;

        schedule.value = true; // Set to false after loading is complete
    } catch (error) {
        console.error('Error fetching schedule:', error.response.data);
        isLoading.value = false;
        schedule.value = false; // Ensure to set loading to false even if there's an error
    }
};



// Binds to each BBCProgrammeCards component. When clicked, it will save the details of the 
// card to a variable called selectedProgramme. This is then used later for scraping the songs
const setSelectedProgramme = (programme) => {
    selectedProgramme.value = programme;
};

// When the user clicks the search button, call scrapeSongsFromProgramme to scrape the songs.
// Then search the songs on Spotify to retrieve the songs objects
const searchSongs = async () => {
    if (selectedProgramme.value) {
        // Hide schedule
        schedule.value = false;

        try {
            const playlistId = extractProgrammeId(selectedProgramme.value.playlistDetails.link);
            const response = await axios.get('/api/schedule/programme/details', {
                params: { playlist_id: playlistId }
            });

            // Check if the response and data property exist
            if (response && response.data) {
                songs.value = response.data.songs.map(song => ({ ...song, checked: true }));
                console.log(songs.value);
                // Show song list
                songList.value = true;
            } else {
                // Handle the case where response does not have a data property
                throw new Error('No data returned from the API');
            }
            selectedProgramme.value = null;
        } catch (error) {
            console.error('Error fetching songs and artists:', error.response ? error.response.data : error);
            songList.value = false;
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

// Adds the selected songs the ther users Spotify account. It takes the playlist name
// songs array and passes that to Spotify to create a playlist and populate it with songs
const addToSpotify = async () => {

    if (songs.value.length === 0) return failAlert();
    // Filter the songs that are checked
    const tracksToAdd = songs.value.filter(song => song.checked);

    try {
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
const updateCheckedState = (songToUpdate, isChecked) => {
    const song = songs.value.find(s => s.spotify_uri === songToUpdate.spotify_uri);
    if (song) {
        song.checked = isChecked;
    }
};

// Function to toggle select all/deselect all songs an updates the checked property
const checkAll = () => {
    isCheckAll.value = !isCheckAll.value;
    songs.value.forEach(song => {
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
                                <option value="" disabled selected>Select a Radio Station</option>
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
                            <input id="startDate" class="form-control form-control-lg" type="date" :max="maxDate"
                                v-model="date" />
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
                    </form>
                </div>
            </div>
        </div>


        <div v-if="isLoading">
            <button class="btn btn-primary btn-lg status-loading" type="button" disabled>
                <span class="spinner-grow spinner-grow-sm me-2" aria-hidden="true"></span>
                <span role="status">Loading...</span>
            </button>
        </div>

        <div v-else>
            <button id="scroll-to-bottom-btn" class="btn btn-primary btn-lg" @click="scrollToBottom">
                ↓
            </button>

            <div v-if="songList" class="btn-group d-flex col-lg-3 col-md-4 mx-auto my-3" role="group"
                aria-label="Basic radio toggle button group">
                <input type="radio" class="btn-check" name="btnradio" id="btnradio1" autocomplete="off"
                    :checked="!isCheckAll" @change="checkAll">
                <label class="btn btn-outline-primary" for="btnradio1">Deselect all</label>

                <input type="radio" class="btn-check" name="btnradio" id="btnradio2" autocomplete="off"
                    :checked="isCheckAll" @change="checkAll">
                <label class="btn btn-outline-primary" for="btnradio2">Select all </label>
            </div>

            <div v-if="songList" class="row justify-content-md-center">
                
                <input v-model="query" class="form-control form-control-lg " placeholder="Search your favourite song">

                <SearchPlaylistCards v-for="song in filteredSongs" :key="song.spotify_uri" :title="song.title"
                    :artists="song.artists" :imageUrl="song.image_url" :audioUrl="song.audio_url" :checked="song.checked"
                    @update:checked="updateCheckedState(song, $event)" />

                <div class="fb-share-button col-md-auto" data-href="http://localhost:8000" data-layout="" data-size="">
                    <button class="btn btn-primary btn-lg my-3">
                        <a target="_blank"
                            href="https://www.facebook.com/sharer/sharer.php?u=http%3A%2F%2Flocalhost:8000%2F&amp;src=sdkpreparse"
                            class="fb-xfbml-parse-ignore link-light link-underline link-underline-opacity-0"> 👉 Share my
                            website to Facebook!
                        </a>
                    </button>
                </div>
            </div>

            <div v-if="schedule">
                <BBCProgrammeCards v-for="programme in programmeList" :key="programme.playlistDetails.link"
                    :link="programme.playlistDetails.link" :title="programme.playlistDetails.primary_title"
                    :secondaryTitle="programme.playlistDetails.secondary_title"
                    :synopsis="programme.playlistDetails.synopsis" :image="programme.playlistDetails.image_url"
                    :isSelected="selectedProgramme === programme" @click="searchSongs"
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

.form-control {
    width: 100%;
}

/* Highlight the selected card */
.programme-card:hover {
    background-color: #e9ecef;
}
</style>
