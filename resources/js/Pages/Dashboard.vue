<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';

const fakeData = ref({
    Name: 'John Doe',
    Address: '123 Main St, Anytown, UK',
    Avatar: 'assets/Sound2.png', // Assuming this is a static display element
    Number: '(+44) 1233455',
    Email: 'john.doe@example.co.uk'
});
</script>

<template>
    <Head title="Profile"/>

    <MainLayout>
        <div class="container my-4">
            <form class="row g-3">
                <div class="col-md-6">
                    <label for="inputEmail" class="form-label">Email</label>
                    <input type="email" class="form-control" id="inputEmail" v-model="fakeData.Email">
                </div>
                <div class="col-md-6">
                    <label for="inputNumber" class="form-label">Phone Number</label>
                    <input type="text" class="form-control" id="inputNumber" v-model="fakeData.Number">
                </div>
                <div class="col-12">
                    <label for="inputAddress" class="form-label">Address</label>
                    <input type="text" class="form-control" id="inputAddress" v-model="fakeData.Address">
                </div>
                <div class="col-12">
                    <!-- Additional field for Address 2 if needed -->
                </div>
                <!-- Additional fields for City, State, Zip if needed -->
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Update Profile</button>
                </div>
            </form>
        </div>
    </MainLayout>
</template>
