<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';

const fakeData = ref({
    Name: 'John Doe',
    Address: '123 Main St, Anytown, UK',
    Avatar: 'assets/Sound2.png',
    Number: '(+44) 1233455',
    Email: 'john.doe@example.co.uk'
});
</script>

<template>
    <Head title="Profile" />

    <MainLayout>
        <div class="container my-4">
            <div class="row">
                <div class="col-md-4">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 450 450">
                        <path
                            d="M224 256A128 128 0 1 0 224 0a128 128 0 1 0 0 256zm-45.7 48C79.8 304 0 383.8 0 482.3C0 498.7 13.3 512 29.7 512H418.3c16.4 0 29.7-13.3 29.7-29.7C448 383.8 368.2 304 269.7 304H178.3z" />
                    </svg>
                    <label for="profileAvatar" class="form-label mt-3">Upload your profile image here:</label>
                    <input type="file" class="form-control" id="profileAvatar">
                </div>
                <div class="col-md-8">
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
                            <button type="submit" class="btn btn-primary">Update Profile</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </MainLayout>
</template>
