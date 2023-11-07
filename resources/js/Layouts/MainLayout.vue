<script setup>

import NavBar from '@/Widgets/NavBar.vue'
import Footer from '@/Widgets/Footer.vue';

    
</script>

<template>
    <div>
        <!-- Navbar -->
        <NavBar />

        <div class="container">
            
            <slot />
        </div>
        <Footer />
    </div>
</template>