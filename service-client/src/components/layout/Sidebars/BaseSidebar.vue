<template>
    <v-list dense>
        <v-list-item>
            <!-- item-value="role_id" -->
            <v-select v-if="hideSwitchAccount" v-model="selectedRole" @update:modelValue="handleChangeRole"
                class="mt-3 mb-3" transition="fab-transition" :items="data_roles" item-title="role_name" item-value="role_id"
                density="compact" variant="outlined" label="Switch Account" append-inner-icon="mdi-account-outline">
            </v-select>
        </v-list-item>

        <template :key="index" v-if="data_roles.length > 0">
            <!-- Xingzheng renyuan -->
            <template
                v-for="(item, index) in sidebarItems">
                <v-list-group v-if="item.children">
                    <template v-slot:activator="{ props }">
                        <v-list-item color="primary" v-bind="props">
                            <template v-slot:prepend>
                                <v-icon class="myIcon">{{ item.icong }}</v-icon>
                            </template>
                            <v-list-item-title color="primary">{{ item.module }}</v-list-item-title>
                        </v-list-item>
                    </template>
                    <v-list-item v-for="(child, childIndex) in item.children" :key="childIndex" :to="{name: child.name}" link>
                        <template v-slot:prepend>
                            <v-icon class="myIcon">{{ child.icong }}</v-icon>
                        </template>
                        <v-list-item-title>{{ child.module }}</v-list-item-title>
                    </v-list-item>
                </v-list-group>
                <v-list-item v-else :to="{name : item.name}" link>
                    <template v-slot:prepend>
                        <v-icon class="myIcon">{{ item.icong }}</v-icon>
                    </template>
                    <v-list-item-title>{{ item.module }}</v-list-item-title>
                </v-list-item>
            </template>

            <!-- Juese -->
            <!-- <template v-for="(item, index) in sidebarItems">
                <v-list-item :to="{ name: item.name }" link>
                    <template v-slot:prepend>
                        <v-icon class="myIcon">{{ item.icong }}</v-icon>
                    </template>
                    <v-list-item-title>{{ item.module }}</v-list-item-title>
                </v-list-item>
            </template> -->
        </template>

        <!-- Default User Role [as Requestor] -> if theres no Roles -->
        <template v-else>
            <template v-for="(item, index) in sidebarConfig.requestor" :key="index">
                <v-list-item :to="item.route" link>
                    <template v-slot:prepend>
                        <v-icon class="myIcon">{{ item.icon }}</v-icon>
                    </template>
                    <v-list-item-title>{{ item.name }}</v-list-item-title>
                </v-list-item>
            </template>
        </template>

    </v-list>
    <v-btn @click="createPost" v-if="can('create', 'Post')">Great Post</v-btn>
</template>
<script setup>
import { computed, ref, onMounted, watch, reactive, } from 'vue';
import { adminSidebarConfig } from '@/helpers/sideBarConfig.js'

import { user_data } from '@/stores/auth/userData'
import { getRole } from '@/stores/getRole'
import { apiRequestAxios } from '@/api/api';
import * as pub_const from '@/global/global.js';
import { useRouter, useRoute } from 'vue-router';
import { useAbility } from '@casl/vue';

/** Role Data Store */
const role = getRole()
role.getRoleData

// const ability = computed(() => role.abilities)
// const { can } = useAbility(ability.value)
const can = (action, subject) => {
    return role.abilities.can(action, subject)
}

const createPost = () =>{
    console.log("great is working")
}

const router = useRouter()
const route = useRoute()

/**Logout Components */
import { logout } from '@/stores/auth/logout'
const logMeOut = logout()

/**User Data Store */
const user = user_data()
user.getUserData
const user_roles = user.user.user_roles
const apiRequest = apiRequestAxios()





/** Decalarations */
const selectedRole = ref(null)
const data_roles = ref([{ role_id: 357, role_name : 'Requestor' }]) //subject to change ->roleID -> 357 is Requestor
const hideSwitchAccount = ref(false)


/** Handle Change of Role */
const handleChangeRole = async () => {
    router.push('/', { replace: true });
    const getRoleName = user.user.user_roles.filter(v=> selectedRole.value === v.role_id).map(v=> v.role_name)
    // console.log(getRoleName)
    role.setCurrentUserRole({role_id : selectedRole.value, role_name : getRoleName});
};


const updatedUserData = ref(null)
const sidebarItems = computed(() => {
    // return sidebarConfig[role.currentUserRole] || []
    updatedUserData.value = user.user.user_roles

    const dashboard = { "module": "Dashboard", "create": true, "read": true, "edit": true, "delete": true, "approve": true, "delegate": true, "installer": true, "report": false, "name": "dashboard", "icong": "mdi-view-dashboard-variant" }
    const requestor = [
        { "module": "Equipment Handling", "create": true, "read": true, "edit": false, "delete": false, "approve": false, "delegate": false, "installer": false, "report": false, "name": "EquipmentHandling", "icong": "mdi-file-document-edit" },
    ]
    // Find the current user's role data
    const currentRoleData = updatedUserData.value.find(roleData => role.currentUserRole.role_id === roleData.role_id);
    // console.log(currentRoleData)
    const dashboardPage = [dashboard]
    if (currentRoleData) {
        const rolePermission = JSON.parse(currentRoleData?.permissions)
        const filteredPermission = rolePermission?.filter(permission => permission.read) || []
        if(currentRoleData.role_id === 6){
            return [...adminSidebarConfig]
        }
        return [...dashboardPage, ...filteredPermission]
    }else {
        return [...dashboardPage, ...requestor,]
    }
});





// onBeforeRouteLeave((to, from, next) => {
//     console.log(data_roles.value)
//         const validRoles = data_roles.value.map(r => r.role_name) //subject to change [role_name] to roleID
//        console.log(validRoles)
//        console.log(!validRoles.includes(role.currentUserRole))
//        console.log(role.currentUserRole)
//        if (!validRoles.includes(role.currentUserRole)) //Change 'Administrator' later to 6[RoleID]
//         {
//             return
//         }else{
//             next()
//         }
// })

onMounted(() => {
    data_roles.value.push(...user.user.user_roles)
    hideSwitchAccount.value = data_roles.value.length === 1 ? false : true

    sidebarItems

    selectedRole.value = role.currentUserRole.role_id ?? ''
})
</script>

































<style scoped>
.myIcon {
    font-size: 25px;
}

.v-navigation-drawer__content .v-list-item__prepend>.v-icon~.v-list-item__spacer {
    width: 5px !important;
}

.v-navigation-drawer__content .v-list-item--density-default.v-list-item--one-line {
    min-height: 0 !important;
}

.v-list-item--density-default.v-list-item--one-line {
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

.v-select .v-select__selection-text {
    font-size: 10px !important;
}
</style>