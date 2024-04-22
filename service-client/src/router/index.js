import { createRouter, createWebHistory } from 'vue-router'
import Dashboard from '../views/Dashboard.vue'
import Login from '../views/Login.vue'
// import EquipmentHandling from '../views/EquipmentHandling/EquipmentHandling.vue'
// import WorkOrder from '../views/EquipmentHandling/WorkOrder.vue'
// import ApprovalConfiguration from '../views/Admin/ApprovalConfiguration.vue'
// import Roles from '../views/Admin/Roles.vue'
// import DashboardApprover from '../views/Approver/DashboardApprover.vue'
// import EquipmentHandlingApprover from '../views/Approver/EquipmentHandlingApprover.vue'
// import WorkOrderApprover from '../views/Approver/WorkOrderApprover.vue'
// import ViewWorkOrder from '../views/EquipmentHandling/ViewWorkOrder.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'login',
      // component: () => import('../views/Login.vue'),
      component : Login,
    },
    {
      path: '/dashboard',
      name: 'dashboard',
      // route level code-splitting
      // this generates a separate chunk (About.[hash].js) for this route
      // which is lazy-loaded when the route is visited.
      // component: () => import('../views/Dashboard.vue'),
      component: Dashboard,
      meta: {
        requiresAuth: true
      }
    },

    /** Equipment Handling */
    {
      path: '/equipment-handling',
      name: 'EquipmentHandling',
      component: () => import('../views/EquipmentHandling/EquipmentHandling.vue'),
      meta: {
        requiresAuth: true
      }
    },
    {
      path: '/work-order',
      name: 'WorkOrder',
      component: () => import('../views/EquipmentHandling/WorkOrder.vue'),
      meta: {
        requiresAuth: true
      }
    },
    {
      path: '/view-work-order/:id',
      name: 'ViewWorkOrder',
      component: () => import('../views/EquipmentHandling/ViewWorkOrder.vue'),
      props : true,
      meta: {
        requiresAuth: true
      }
    },


    /** Admin Routes */
    {
      path: '/aprroval-config',
      name: 'ApprovalConfiguration',
      component: () => import('../views/Admin/ApprovalConfiguration.vue'),
      meta: {
        requiresAuth: true
      }
    },
    {
      path: '/roles',
      name: 'Roles',
      component: () => import('../views/Admin/Roles.vue'),
      meta: {
        requiresAuth: true
      }
    },


    /** Approver Routes */
    {
      path:'/approver-dashboard',
      name : 'DashboardApprover',
      component : () => import('../views/Approver/DashboardApprover.vue'),
      meta: {
        requiresAuth: true
      }
    },
    {
      path:'/approver-equipment-handling',
      name : 'EquipmentHandlingApprover',
      component : () => import('../views/Approver/EquipmentHandlingApprover.vue'),
      meta: {
        requiresAuth: true
      }
    },
    {
      path:'/approver-work-order/:id',
      name : 'WorkOrderApprover',
      component : () => import('../views/Approver/WorkOrderApprover.vue'),
      props : true,
      meta: {
        requiresAuth: true
      }
    },
  ]
})

router.beforeEach((to, from, next) => {
  const authenticated = JSON.parse(localStorage.getItem('isAuthenticated'))

  if (to.meta.requiresAuth && !authenticated) {
    next({ path: '/' })
  } else if (to.path === '/' && authenticated) {
    next({ path: '/dashboard' })
  } else {
    next();
  }
})

export default router
