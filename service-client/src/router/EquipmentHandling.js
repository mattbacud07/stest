import { createRouter, createWebHistory } from 'vue-router'

const router = createRouter({
    history: createWebHistory(import.meta.env.BASE_URL),
    routes: [

        /** Equipment Handling */
        {
            path: '/equipment-handling',
            name: 'EquipmentHandling',
            component: () => import('../views/EquipmentHandling/EquipmentHandling.vue'),
            meta: {
                requiresAuth: true
            },
            children: [
                {
                    path: '/work-order',
                    name: 'WorkOrder',
                    component: () => import('../views/EquipmentHandling/WorkOrder.vue'),
                    meta: {
                        requiresAuth: true,
                        requiresAccess: true,
                    }
                },
            ]
        },

    ]
})

router.beforeEach((to, from, next) => {
    const authenticated = JSON.parse(localStorage.getItem('isAuthenticated'))

    if (to.meta.requiresAuth && !authenticated) {
        if(to.meta.requiresAccess && !from.path.startsWith('/equipment-handling')){
            next('/equipment-handling')
        }
        next({ path: '/' })
    } else if (to.path === '/' && authenticated) {
        next({ path: '/dashboard' })
    } else {
        next();
    }
})

export default router
