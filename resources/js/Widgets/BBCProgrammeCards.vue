<script setup>
import { defineProps, defineEmits } from 'vue';

const props = defineProps({
    image: String,
    title: String,
    secondaryTitle: String,
    synopsis: String,
    link: String,
});

// Define the event that this component can emit
const emit = defineEmits(['checked']);

// Emit the 'checked' event with the programme details when the component is clicked
const checkProgramme = () => {
    emit('checked', {
        image: props.image,
        title: props.title,
        secondaryTitle: props.secondaryTitle,
        synopsis: props.synopsis,
        link: props.link
    });
};
</script>

<template>
    <div class="row align-items-center border shadow mt-5 bg-body-tertiary rounded mx-0" @click="checkProgramme">
        <!-- Set the image column to be smaller and add a right padding -->
        <div class="col-md-4 ps-0 pe-2">
            <img id="image" class="rounded" :src="props.image">
        </div>
        <!-- Set the text column to take up the remaining space and add left padding -->
        <div class="col-md-8 pe-0 ps-2">
            <p id="card-title">{{ title }}</p>
            <p id="card-secondary-title"><i>{{ secondaryTitle }}</i></p>
            <p><i>{{ synopsis }}</i></p>
        </div>
    </div>
</template>




<style scoped>
#card-title {
    font-size: 2.25rem;
}

#image {
    width: 200px;
    height: auto;

}

#card-secondary-title {
    font-size: 1.5rem;
}

</style>