<template>
    <v-app dense>
        <v-app-bar>
            <v-app-bar-nav-icon color="primary" @click="drawer = !drawer"></v-app-bar-nav-icon>

            <v-app-bar-title :style="{ display: width <= 426 ? 'none' : '' }">Service <span
                    style="color:#191970;font-weight:700;">webApp</span></v-app-bar-title>
            <p :style="{ display: width <= 426 ? 'block' : 'none', }">service<span
                    style="color:#191970;font-weight:700;">App</span></p>

            <v-spacer></v-spacer>
            <topBarUserProfile />
        </v-app-bar>

        <v-navigation-drawer v-model="drawer">
            <!--  -->
            <BaseSidebar />
        </v-navigation-drawer>

        <v-main>
            <!-- Fixed toolbar -->
            <v-toolbar class="fixed-toolbar border-b-sm" dense color="white"
                :style="{ width: width < 1280 || drawer === false ? '100%' : 'calc(100% - 256px)' }">


                <v-spacer v-if="width > 550"></v-spacer>

                <!-- Column Chooser  -->
                <v-menu v-model="columnChooser" :close-on-content-click="false" location="bottom">
                    <template v-slot:activator="{ props }">
                        <v-btn color="primary" variant="plain" icon v-bind="props" class="text-none">
                            <v-icon>mdi-chevron-down</v-icon> </v-btn>
                    </template>

                    <v-card min-width="auto" class="pr-3">
                        <v-checkbox class="columnChooserCheckBox" :label="col.title" v-for="col in column"
                            :key="col.field" :model-value="!col.hide"
                            @change="col.hide = !$event.target.checked"></v-checkbox>
                    </v-card>
                </v-menu>

                <!-- Search Master Data -->
                <v-text-field v-model="searchInput" @input="searching" order-sm="1" density="compact" class="ml-3"
                    variant="plain" prepend-inner-icon="mdi-magnify" color="primary"
                    placeholder="Search data"></v-text-field>


                <v-btn @click="handleRefresh" color="lighten-3" variant="text" class="mr-1 text-none">
                    <v-icon class="mr-1">mdi-refresh</v-icon> {{ width < 768 ? '' : ' Refresh' }} </v-btn>


                        <v-btn variant="tonal" :disabled="btnDisable" @click="dialogDelete = true" color="error"
                            v-if="enableDelete" class="text-none mr-1">
                            <v-icon>mdi-trash-can</v-icon> {{ width < 768 ? '' : 'Delete' }} <v-dialog
                                v-model="dialogDelete" max-width="340">
                                <template v-slot:default="{ isActive }">
                                    <v-card prepend-icon="mdi-trash-can" text="Are you sure you want to delete?"
                                        title="Delete">
                                        <template v-slot:actions>
                                            <v-btn class="ml-auto text-none" text="Cancel"
                                                @click="dialogDelete = false"></v-btn>
                                            <v-btn @click="handleDelete" variant="flat" :disabled="btnDisable"
                                                :loading="btnDeleteLoading" color="error"
                                                class="text-none mr-1">Delete</v-btn>
                                        </template>
                                    </v-card>
                                </template>
                                </v-dialog>
                        </v-btn>


                        <v-btn @click="handleView" variant="tonal" :disabled="btnDisable" color="primary"
                            class="text-none mr-1">
                            <v-icon>
                                mdi-file-eye
                            </v-icon>
                            {{ width < 768 ? '' : 'View' }} </v-btn>

                                <v-btn @click="handleEdit" color="primary" variant="tonal" class="text-none mr-1"
                                    :disabled="btnDisable">
                                    <v-icon>mdi-pencil</v-icon>
                                    {{ width < 768 ? '' : ' Edit' }} </v-btn>
                                        <v-btn @click="handleCreate" v-if="details?.createRequest" color="primary"
                                            variant="flat" class="text-none"> <!--- v-if="enableCreate"-->
                                            <v-icon>mdi-plus</v-icon>
                                            {{ width < 768 ? '' : 'Create' }} </v-btn>
            </v-toolbar>

            <!-- Main content slot -->
            <div class="mainWrapper">

                <!-- Dynamic Filtering -->
                <slot name="filter"></slot>

                <slot :searchText='searchInput'></slot>
            </div>
        </v-main>

    </v-app>
</template>

                                <script setup>
                                import { ref, inject, provide, watch, onMounted, defineEmits, computed } from 'vue'
                                import BaseSidebar from '../Sidebars/BaseSidebar.vue';
                                import topBarUserProfile from './LayoutParts/topBarUserProfile.vue'
                                import { useRouter, useRoute } from 'vue-router';
                                import { useDisplay } from 'vuetify'
                                import { getRole } from '@/stores/getRole'

                                /** CAstl Permission */
                                import { permit } from '@/castl/permitted';
                                const { can } = permit()

                                const router = useRouter()
                                const route = useRoute()
                                const drawer = ref(null)
                                const { width, mobile } = useDisplay()
                                const columnChooser = ref(null)
                                const role = getRole()
                                const currentUserRole = role.currentUserRole

                                const details = inject('data') //this is from Views Pages
                                const refresh = inject('refresh', null)
                                const column = inject('column', null)
                                const btnDisable = ref(details.btnDisable ?? true)
                                const btnDeleteLoading = ref(false)
                                const dialogDelete = ref(false)
                                // const enableCreate = ref(false)
                                const searchInput = ref('')
                                const emits = defineEmits(['searchText'])
                                const searching = (e) => {
                                    emits('searchText', e.target.value)
                                }

                                // const service_id = 'service_id' in details ? details.service_id.value : null
                                // const enableCreateFunction = () => {
                                //     if (route.name === 'EquipmentHandling') {
                                //         return enableCreate.value = true
                                //     }
                                // }

                                /** Handle Table Refresh */
                                const handleRefresh = () => {
                                    if (typeof refresh === 'function') {
                                        refresh()
                                    }
                                }

                                const enableDelete = computed(() => {
                                    if (details?.deleteFunction && typeof details.deleteFunction === 'function') {
                                        return true
                                    }
                                    return false
                                })

                                const handleDelete = async (dialog) => {
                                    if (details?.deleteFunction && typeof details.deleteFunction === 'function') {
                                        btnDeleteLoading.value = true
                                        try {
                                            await details.deleteFunction();
                                            dialogDelete.value = false
                                        } catch (error) {
                                            console.error('Error in deleteFunction:', error);
                                            // Optionally show an error message to the user
                                            toast.error('Failed to delete. Please try again.');
                                        } finally {
                                            btnDeleteLoading.value = false; // Ensures the loading state resets
                                        }
                                    }
                                }


                                /** Handle View Redirection */
                                const handleView = () => {
                                    const params = {
                                        id: details?.selectedId.value
                                    }
                                    // alert( params.id +' '+ details.routeView.value)
                                    // if (service_id !== null) {
                                    //     params.service_id = service_id;
                                    // }
                                    // console.log(details.work_type)
                                    // if (details.work_type !== null && details.work_type !== '') {
                                    //     params.work_type = details.work_type
                                    // }

                                    router.push({ name: details?.routeView.value, params });
                                }


                                /** Hnadle Create Redirection */
                                const handleCreate = () => {
                                    if (details?.createRequest) {
                                        const params = {}
                                        if (details.work_type !== null && details.work_type !== '') {
                                            params.work_type = details.work_type
                                        }
                                        router.push({ name: details.createRequest, params })
                                    }
                                }


                                /**Handle Edit Redirection */
                                const handleEdit = () => {
                                    if (details?.editRequest) {
                                        const params = {
                                            id: details?.selectedId.value
                                        }
                                        router.push({ name: details.editRequest, params })
                                    }
                                }


                                // onMounted(() => {
                                //     enableCreateFunction()
                                // })
</script>


                                <style scoped>
                                .fixed-toolbar {
                                    position: fixed;
                                    z-index: 100;
                                    right: 0;
                                }

                                .mainWrapper {
                                    /* display: flex; */
                                    justify-content: center;
                                    align-items: center;
                                    padding-top: 60px;
                                    /* Adjust based on your toolbar height */
                                    margin: 20px !important;
                                }

                                .columnChooserCheckBox {
                                    height: 40px !important;
                                }
                            </style>