<template>
    <v-list dense>
        <v-list-item>
            <!-- <template v-slot:prepend>
                <v-icon class="myIcon">mdi-account-card</v-icon>
            </template> -->
            <v-select v-if="hideSwitchAccount" v-model="selectedRole" @update:modelValue="handleChangeRole" class="mt-3 mb-3" transition="fab-transition"
               :items="data_roles" item-title="role_name"  item density="compact" variant="outlined" label="Switch Account"
               append-inner-icon="mdi-account-outline">
            </v-select>
            <!-- hide-selected hide-no-data  -->
        </v-list-item> 

        <template v-for="(item, index) in sidebarItems" :key="index">
            <v-list-group v-if="item.children">
                <template v-slot:activator="{ props }">
                    <v-list-item color="primary" v-bind="props">
                        <template v-slot:prepend>
                            <v-icon class="myIcon">{{ item.icon }}</v-icon>
                        </template>
                        <v-list-item-title color="primary">{{ item.name }}</v-list-item-title>
                    </v-list-item>
                </template>
                <v-list-item v-for="(child, childIndex) in item.children" :key="childIndex" :to="child.route" link>
                    <template v-slot:prepend>
                        <v-icon class="myIcon">{{ child.icon }}</v-icon>
                    </template>
                    <v-list-item-title>{{ child.name }}</v-list-item-title>
                </v-list-item>
            </v-list-group>
            <v-list-item v-else :to="item.route" link>
                <template v-slot:prepend>
                    <v-icon class="myIcon">{{ item.icon }}</v-icon>
                </template>
                <v-list-item-title>{{ item.name }}</v-list-item-title>
            </v-list-item>
        </template>
    </v-list>
</template>
<script setup>
import { computed, ref, onMounted, watch } from 'vue';
import { sidebarConfig } from '@/helpers/sideBarConfig.js'

import { user_data } from '@/stores/auth/userData'
import { getRole } from '@/stores/getRole'
import * as pub_const from '@/global/global.js';
import { useRouter, useRoute } from 'vue-router';
// import { onBeforeUnmount } from 'vue';

// onBeforeUnmount(() => {
//     window.addEventListener('beforeunload', () => {
//         localStorage.clear(); // Or sessionStorage.clear();
//     });
// });

const router = useRouter()
const route = useRoute()

const user = user_data()
user.getUserData
const apiRequest = user.apiRequest()

const role = getRole()
role.getRoleData

const selectedRole = ref('')
const data_roles = ref(['Requestor'])
const hideSwitchAccount = ref(false)
// const currentRole = ref('guest')

/** Hande Change of Role */
const handleChangeRole = () => {
    // switch (selectedRole.value) {
    //     case pub_const.adminServiceRole:
    //         router.push('/', { replace: true });
    //         break;
    //     case pub_const.approverRole:
    //         router.push('/', { replace: true });
    //         break;
    //     case pub_const.engineerRole:
    //         router.push('/', { replace: true });
    //         break;
    //     case pub_const.serviceTLRole:
    //         router.push('/', { replace: true });
    //         break;
    //     case pub_const.outboundPersonnel:
    //         router.push('/', { replace: true });
    //         break;
    //     default:
    //         router.push('/', { replace: true });
    //         break;
    // }
    router.push('/', { replace: true });
    role.setCurrentUserRole(selectedRole.value);
    console.log(selectedRole.value)
};


const sidebarItems = computed(() => {
    return sidebarConfig[role.currentUserRole] || [];
});

// const getRoleName =  async (role_id) =>{
//             try {
//                 const response = await apiRequest.get('get_role_name')
//                 if(response.data && response.data.role_name){
//                     const rName = response.data.role_name
//                     const role_name = rName.find(e => e.id === role_id)
//                     return role_name.role_name
//                     console.log(role_name.role_name) 
//                 }
//             } catch (error) {
//                 console.log(error)
//             }
//         }

onMounted(() => {
    data_roles.value.push(...user.user.user_roles)
    hideSwitchAccount.value = data_roles.value.length === 1 ? false : true

    sidebarItems

    selectedRole.value = role.currentUserRole ?? ''
    // getRoleName()
})
</script>













<style scoped>
.myIcon{
    font-size: 25px;
}

.v-navigation-drawer__content .v-list-item__prepend > .v-icon ~ .v-list-item__spacer{
   width: 5px!important;
}
.v-navigation-drawer__content .v-list-item--density-default.v-list-item--one-line {
    min-height: 0 !important;
}
.v-list-item--density-default.v-list-item--one-line{
    padding-top: 6px;
    padding-bottom: 6px;
}

.v-navigation-drawer__content .v-list-item-title {
    font-size: 11.5px !important;
    font-weight: 600 !important;
    color: #191970;
    /* text-overflow: clip; */
}

.v-navigation-drawer__content .v-icon {
    color: #191970 !important;
    opacity: 1 !important;
}

.v-navigation-drawer__content .v-list-group__items .v-list-item {
    padding-left: 30px !important;
}
.v-list-item__prepend {
    display: none !important;
}
.v-select .v-select__selection-text{
    font-size: 10px!important;
}
</style>