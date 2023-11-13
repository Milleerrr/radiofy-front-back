<script setup>

</script>

<template>
    <div class="row row-cols-1 row-cols-md-3 g-4 my-5">
        <div class="col">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Simple</h5>
                    <img src="../../../public/assets/simple.jpg" class="card-img-top img-fluid" alt="#">
                    <p class="card-text">Radiofy offers a one-click solution for adding Radio 1 playlists to Spotify, making
                        music curation effortless and instant for users.</p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Easy</h5>
                    <img src="../../../public/assets/easy.jpg" class="card-img-top img-fluid" alt="#">
                    <p class="card-text">Login with Spotify and in one click, your Spotify account swells with Radio 1's
                        latest tracks.</p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Done</h5>
                    <img src="../../../public/assets/headphones.jpg" class="card-img-top img-fluid" alt="#">
                    <p class="card-text">Sit back, relax and enjoy listening to Radio 1 tunes for ever and ever!</p>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>

.card-img-top {
    height: 200px;
    width: 100%;
    object-fit: cover;
    margin-bottom: 1rem;
}

.card {
    margin: 1.5rem;
}

.card {
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    transition: transform 0.5s;
}

.card:hover {
    transform: translateY(-10px);
    box-shadow: 0 10px 15px rgba(3, 91, 255, 0.726);
    cursor: pointer;
}


</style>