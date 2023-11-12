<script setup>
import { ref, defineProps, defineEmits, watchEffect } from 'vue';

const props = defineProps({
    title: String,
    artists: String,
    imageUrl: String,
    audioUrl: String,
    checked: Boolean
});

const emits = defineEmits(['update:checked']);

// Use 'checked' prop with v-model
const isChecked = ref(props.checked);

watchEffect(() => {
    isChecked.value = props.checked;
});

</script>

<template>
    <div class="container">
        <div class="row align-items-center border shadow mt-5 bg-body-tertiary rounded">
            <div class="col-md-5 ps-0">
                <img id="song-img" class="rounded" :src="props.imageUrl">
            </div>
            <div class="col-md-5">
                <p id="card-title">{{ props.title }}</p>
                <p id="card-artist"><i>{{ props.artists }}</i></p>
                <div v-if="props.audioUrl">
                    <audio controls>
                        <source type="audio/mp3" :src="props.audioUrl">
                        Your browser does not support the audio element.
                    </audio>
                </div>
                <div v-else>
                    <span>No audio preview available for this song</span>
                </div>
            </div>
            <div class="col-md-2">
                <div class="checkbox-wrapper-12">
                    <div class="cbx">
                        <input id="cbx-12" type="checkbox" v-model="isChecked" @change="$emit('update:checked')" />
                        <label for="cbx-12"></label>
                        <svg width="30" height="30" viewBox="0 0 15 14" fill="none">
                            <path d="M2 8.36364L6.23077 12L13 2"></path>
                        </svg>
                    </div>
                    <!-- Gooey-->
                    <svg xmlns="http://www.w3.org/2000/svg" version="1.1">
                        <defs>
                            <filter id="goo-12">
                                <fegaussianblur in="SourceGraphic" stddeviation="4" result="blur"></fegaussianblur>
                                <fecolormatrix in="blur" mode="matrix" values="1 0 0 0 0  0 1 0 0 0  0 0 1 0 0  0 0 0 22 -7"
                                    result="goo-12"></fecolormatrix>
                                <feblend in="SourceGraphic" in2="goo-12"></feblend>
                            </filter>
                        </defs>
                    </svg>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
#song-img {
    width: 60%;
    height: auto;
}

#card-title {
    font-size: 2.25rem;
}

#card-artist {
    font-size: 1.5rem;
}

.checkbox-wrapper-12 {
    position: relative;
}

.checkbox-wrapper-12>svg {
    position: absolute;
    top: -130%;
    left: -170%;
    width: 110px;
    pointer-events: none;
}

.checkbox-wrapper-12 * {
    box-sizing: border-box;
}

.checkbox-wrapper-12 input[type="checkbox"] {
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    -webkit-tap-highlight-color: transparent;
    cursor: pointer;
    margin: 0;
}

.checkbox-wrapper-12 input[type="checkbox"]:focus {
    outline: 0;
}

.checkbox-wrapper-12 .cbx {
    width: 48px;
    height: 48px;
    top: calc(50vh - 12px);
    left: calc(50vw - 12px);
}

.checkbox-wrapper-12 .cbx input {
    position: absolute;
    top: 0;
    left: 0;
    width: 48px;
    height: 48px;
    border: 2px solid #bfbfc0;
    border-radius: 50%;
}

.checkbox-wrapper-12 .cbx label {
    width: 48px;
    height: 48px;
    background: none;
    border-radius: 50%;
    position: absolute;
    top: 0;
    left: 0;
    -webkit-filter: url("#goo-12");
    filter: url("#goo-12");
    transform: trasnlate3d(0, 0, 0);
    pointer-events: none;
}

.checkbox-wrapper-12 .cbx svg {
    position: absolute;
    top: 50%;
    /* Center vertically */
    left: 15%;
    /* Center horizontally */
    transform: translate(-50%, -50%);
    /* Adjust for exact centering */
    z-index: 1;
    pointer-events: none;
}

.checkbox-wrapper-12 .cbx svg path {
    stroke: #fff;
    stroke-width: 3;
    stroke-linecap: round;
    stroke-linejoin: round;
    stroke-dasharray: 19;
    stroke-dashoffset: 19;
    transition: stroke-dashoffset 0.3s ease;
    transition-delay: 0.2s;
}

.checkbox-wrapper-12 .cbx input:checked+label {
    animation: splash-12 0.6s ease forwards;
}

.checkbox-wrapper-12 .cbx input:checked+label+svg path {
    stroke-dashoffset: 0;
}

@-moz-keyframes splash-12 {
    40% {
        background: rgb(5, 180, 34);
        box-shadow: 0 -36px 0 -16px rgb(5, 180, 34), 32px -16px 0 -16px rgb(5, 180, 34), 32px 16px 0 -16px rgb(5, 180, 34), 0 36px 0 -16px rgb(5, 180, 34), -32px 16px 0 -16px rgb(5, 180, 34), -32px -16px 0 -16px rgb(5, 180, 34);
    }

    100% {
        background: rgb(5, 180, 34);
        box-shadow: 0 -72px 0 -20px transparent, 64px -32px 0 -20px transparent, 64px 32px 0 -20px transparent, 0 72px 0 -20px transparent, -64px 32px 0 -20px transparent, -64px -32px 0 -20px transparent;
    }
}

@-webkit-keyframes splash-12 {
    40% {
        background: rgb(5, 180, 34);
        box-shadow: 0 -36px 0 -16px rgb(5, 180, 34), 32px -16px 0 -16px rgb(5, 180, 34), 32px 16px 0 -16px rgb(5, 180, 34), 0 36px 0 -16px rgb(5, 180, 34), -32px 16px 0 -16px rgb(5, 180, 34), -32px -16px 0 -16px rgb(5, 180, 34);
    }

    100% {
        background: rgb(5, 180, 34);
        box-shadow: 0 -72px 0 -20px transparent, 64px -32px 0 -20px transparent, 64px 32px 0 -20px transparent, 0 72px 0 -20px transparent, -64px 32px 0 -20px transparent, -64px -32px 0 -20px transparent;
    }
}

@-o-keyframes splash-12 {
    40% {
        background: rgb(5, 180, 34);
        box-shadow: 0 -36px 0 -16px rgb(5, 180, 34), 32px -16px 0 -16px rgb(5, 180, 34), 32px 16px 0 -16px rgb(5, 180, 34), 0 36px 0 -16px rgb(5, 180, 34), -32px 16px 0 -16px rgb(5, 180, 34), -32px -16px 0 -16px rgb(5, 180, 34);
    }

    100% {
        background: rgb(5, 180, 34);
        box-shadow: 0 -72px 0 -20px transparent, 64px -32px 0 -20px transparent, 64px 32px 0 -20px transparent, 0 72px 0 -20px transparent, -64px 32px 0 -20px transparent, -64px -32px 0 -20px transparent;
    }
}

@keyframes splash-12 {
    40% {
        background: rgb(5, 180, 34);
        box-shadow: 0 -36px 0 -16px rgb(5, 180, 34), 32px -16px 0 -16px rgb(5, 180, 34), 32px 16px 0 -16px rgb(5, 180, 34), 0 36px 0 -16px rgb(5, 180, 34), -32px 16px 0 -16px rgb(5, 180, 34), -32px -16px 0 -16px rgb(5, 180, 34);
    }

    100% {
        background: rgb(5, 180, 34);
        box-shadow: 0 -72px 0 -20px transparent, 64px -32px 0 -20px transparent, 64px 32px 0 -20px transparent, 0 72px 0 -20px transparent, -64px 32px 0 -20px transparent, -64px -32px 0 -20px transparent;
    }
}</style>
  