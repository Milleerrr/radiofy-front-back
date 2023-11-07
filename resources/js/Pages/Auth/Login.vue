<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>

    <Head title="Login page" />

    <MainLayout>
        <div class="container border shadow mb-5 bg-body-tertiary rounded">
            <div class="row">
                <div class="col py-3">
                    <router-link class="navbar-brand" to="/">
                        <img src="/assets/radiofy.svg" alt="Radiofy"> Radiofy
                    </router-link>
                    <div id="login" class="d-flex flex-column align-items-center">
                        <div class="login-section">
                            <h2>Log in with</h2>
                            <div class="socials my-3">
                                <Link as="button" type="button" class="social-link">
                                    <img src="/assets/google-logo.png" alt="Google Logo" class="social-icon">
                                </Link>
                                <Link as="button" type="button" class="social-link">
                                    <img src="/assets/spotify-logo.png" alt="Spotify Logo" class="social-icon">
                                </Link>
                            </div>
                            <p>Or login in with</p>
                            <hr>
                            <form class="form-login">
                                <input class="form-control my-2" placeholder="Email" type="email">
                                <input class="form-control my-2" placeholder="Password" type="password">
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <div>
                                        <input class="me-2" type="checkbox" id="remember-me">
                                        <label for="remember-me">Remember me</label>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Sign in</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col g-0">
                    <img class="img-fluid" src="/assets/dj.png" alt="Flower">
                </div>
            </div>
        </div>
    </MainLayout>
</template>

<style scoped>
#login {
    top: 2rem;
}

.login-section {
    max-width: 400px;
    width: 100%;
    padding: 2rem;
    text-align: center;
}

.socials {
    display: flex;
    justify-content: center;
    gap: 1rem;
}

.social-link {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 48px;
    height: 48px;
    border-radius: 50%;
    overflow: hidden;
    border: none;
}

.social-icon {
    width: 100%;
    height: auto;
}

.form-login {
    display: flex;
    flex-direction: column;
}

.form-control {
    border-radius: 4px;
    border: 1px solid #ced4da;
    padding: 0.375rem 0.75rem;
    font-size: 1rem;
}

.btn-primary {
    color: #fff;
    background-color: #007bff;
    border-color: #007bff;
    padding: 0.375rem 0.75rem;
    border-radius: 4px;
    font-size: 1rem;
    transition: background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.btn-primary:hover {
    background-color: #0069d9;
    border-color: #0062cc;
}

/* Add more styles based on your design requirements */
</style>
