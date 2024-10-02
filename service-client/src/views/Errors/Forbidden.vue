<template>
    <v-row>
        <v-col>
            <v-col class="d-flex justify-content-center flex-column align-items-center">
                <div class="mt-10">
                    <h1><v-icon>mdi-door-closed-lock</v-icon> <span class="text-grey">403 <span class="small">Forbidden</span></span></h1>
                </div>
                <router-link to="/"><v-btn class="mt-5" elevation="1"><v-icon class="mr-3">mdi-arrow-left-bold</v-icon> Go back</v-btn></router-link>
            </v-col>
        </v-col>
    </v-row>
</template>