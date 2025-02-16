<template>
    <v-list dense>
        <v-list-item>
            <!-- item-value="role_id" v-if="hideSwitchAccount" -->
            <v-select v-if="hideSwitchAccount" v-model="selectedRole" @update:modelValue="handleChangeRole"
                class="mt-3 mb-3" transition="fab-transition" :items="user_roles" item-title="role_name"
                item-value="role_id" density="compact" variant="outlined" label="Switch Account"
                append-inner-icon="mdi-account-outline">
            </v-select>
        </v-list-item>

        <template :key="index" v-if="user_roles.length > 0">
            <!-- Xingzheng renyuan -->
           <template  v-for="(item, index) in sidebarItems">
                <v-list-group v-if="item.children">
                    <template v-slot:activator="{ props }">
                        <v-list-item color="primary" v-bind="props">
                            <template v-slot:prepend>
                                <v-icon class="myIcon">{{ item.icong }}</v-icon>
                            </template>
                            <v-list-item-title color="primary">{{ item.module }}</v-list-item-title>
                        </v-list-item>
                    </template>
                    <v-list-item v-for="(child, childIndex) in item.children" :key="childIndex"
                        :to="{ name: child.name }" link active-class="active-link">
                        <template v-slot:prepend>
                            <v-icon class="myIcon">{{ child.icong }}</v-icon>
                        </template>
                        <v-list-item-title>{{ child.module }}</v-list-item-title>
                    </v-list-item>
                </v-list-group>
                <template v-else>
                    <v-list-item :to="{ name: item.name }" link active-class="active-link">
                        <template v-slot:prepend>
                            <v-icon class="myIcon">{{ item.icong }}</v-icon>
                        </template>
                        <v-list-item-title>{{ item.module }}</v-list-item-title>
                    </v-list-item>
                </template>
            </template>
        </template>

        <!-- Default User Role [as Requestor] -> if theres no Roles -->
        <!-- <template v-else>
            <template v-for="(item, index) in sidebarConfig.requestor" :key="index">
                <v-list-item :to="item.route" link>
                    <template v-slot:prepend>
                        <v-icon class="myIcon">{{ item.icon }}</v-icon>
                    </template>
                    <v-list-item-title>{{ item.name }}</v-list-item-title>
                </v-list-item>
            </template>
        </template> -->

    </v-list>
</template>
<script setup>
import { computed, ref, onMounted } from 'vue';
import { adminSidebarConfig } from '@/helpers/sideBarConfig.js'
import { user_data } from '@/stores/auth/userData'
import { getRole } from '@/stores/getRole'
import * as pub_const from '@/global/global.js';
import { useRouter, useRoute } from 'vue-router';
import { useAbility } from '@casl/vue';

import { abilityStore } from '@/stores/abilityStores';
const myAbility = abilityStore()

/** Role Data Store */
const role = getRole()
const currentUserRole = role.currentUserRole

const router = useRouter()

/**User Data Store */
const user = user_data()


/** Decalarations */
const selectedRole = ref(currentUserRole)
const user_roles = ref([])
const hideSwitchAccount = ref(false)

/** Handle Change of Role */
const handleChangeRole = () => {
    role.setCurrentUserRole(selectedRole.value);
    myAbility.buildAbility()
    router.push('/', { replace: true });
};

const sidebarItems = computed(() => {
    const dashboard = { "module": "Dashboard", "create": true, "read": true, "edit": true, "delete": true, "approve": true, "delegate": true, "installer": true, "report": false, "name": "dashboard", "icong": "mdi-view-dashboard-variant" }

    // Find the current user's role data
    const currentRoleData = user_roles.value.find(data => role.currentUserRole === data.role_id);

    const dashboardPage = [dashboard]
    if (currentRoleData) {
        const rolePermission = JSON.parse(currentRoleData?.permissions)
        const filteredPermission = rolePermission?.filter(permission => permission.read) || []
        if (currentRoleData.role_id === pub_const.adminServiceRoleID) {
            return [...adminSidebarConfig]
        }
        else if (currentRoleData.role_id === pub_const.approverRoleID) { // for Approver Link
            
            let modulesToShow = [];
                // Filter the permissions based on user assigned categories
                modulesToShow = filteredPermission.filter(permission => {
                    if (user.user.approval_level.length > 0 && permission.module === "Equipment Handling") return true
                    if (user.user.approval_level_pullout.length > 0 && permission.module === "PullOut Request") return true
                    return false
                })
                return [...dashboardPage, ...modulesToShow];
        }
        return [...dashboardPage, ...filteredPermission]
    }
});



onMounted(() => {
    user_roles.value.push(...user.user.user_roles)
    hideSwitchAccount.value = user_roles.value.length === 1 ? false : true

    sidebarItems
})
</script>

































<style scoped>
.myIcon {
    font-size: 18px;
}

/* .v-navigation-drawer__content .v-list-item__prepend>.v-icon~.v-list-item__spacer {
    width: 5px !important;
} */

.v-navigation-drawer__content .v-list-item--density-default.v-list-item--one-line {
    min-height: 0 !important;
}

.v-list-item--density-default.v-list-item--one-line {
    padding-top: 8px;
    padding-bottom: 8px;
}

.v-navigation-drawer__content .v-list-item-title {
    font-size: 12px !important;
    font-weight: 700 !important;
    color: #191970;
    /* text-overflow: clip; */
}

.v-navigation-drawer__content .v-icon {
    color: #191950 !important;
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

.active-link {
    color: #ffffff !important;
    background-color: #e4e4e4 !important;
    border-left: 5px solid #191970;
}
</style>