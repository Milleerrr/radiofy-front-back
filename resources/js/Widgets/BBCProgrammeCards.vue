<script setup>
import { defineProps, defineEmits } from 'vue';

const props = defineProps({
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
        title: props.title,
        secondaryTitle: props.secondaryTitle,
        synopsis: props.synopsis,
        link: props.link
    });
};
</script>

<template>
    <!-- Add @click handler to emit the event when the div is clicked -->
    <div class="row align-items-center border shadow mt-5 bg-body-tertiary rounded mx-0" @click="checkProgramme">
        <div class="col">
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

#card-secondary-title {
    font-size: 1.5rem;
}
</style>