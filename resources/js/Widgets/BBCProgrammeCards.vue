<script setup>
import { defineProps, defineEmits } from 'vue';

const props = defineProps({
    image: String,
    title: String,
    secondaryTitle: String,
    synopsis: String,
    link: String,
    isSelected: Boolean,
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
        isSelected: props.isSelected,
        link: props.link,
    });
};

</script>

<template>
    <div class="d-flex justify-content-center align-items-center py-3 programme-card"  :class="{ 'selected': isSelected }" 
        @click="checkProgramme"> <!-- Center the card in the container -->
      <div class="card border shadow bg-body rounded w-100 mx-auto" style="max-width: 740px;"> <!-- Set max width for larger screens -->
        <div class="row g-0"> <!-- Bootstrap's grid system -->
          <div class="col-md-4"> <!-- Image takes up 4 out of 12 columns on medium screens -->
            <img :src="props.image" class="img-fluid rounded-start" alt="Programme image"> <!-- Responsive image with rounded corners on the left -->
          </div>
          <div class="col-md-8"> <!-- Content takes up the remaining 8 out of 12 columns on medium screens -->
            <div class="card-body">
              <h5 class="card-title">{{ props.title }}</h5> <!-- Title -->
              <p class="card-text"><small class="text-muted">{{ props.secondaryTitle }}</small></p> <!-- Secondary title -->
              <p class="card-text">{{ props.synopsis }}</p> <!-- Synopsis -->
              <button class="btn btn-success btn-lg" type="button" @click="searchSongs">Search</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </template>


<style scoped>

</style>