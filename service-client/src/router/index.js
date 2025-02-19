import { createRouter, createWebHistory } from 'vue-router'

import { EH, PM, IS, CM, MD, PR } from '@/global/modules'
import {
  canAccess,
  adminGuard
} from './routeGuardBeforeEnter'
import { workOrderMainRequest } from './equipmentHandlingGuard'
import { preventiveMaintenanceRequest } from './preventiveMaintenanceGuard'
import { internalServicingRequest } from './internalServicingGuard'
import PulloutRequest from '@/views/PulloutRequest/PulloutRequest.vue'


const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'login',
      component: () => import('../views/Login.vue')
      // component: Login,
    },

    {
      path: '/dashboard',
      name: 'dashboard',
      component: () => import('../views/Dashboard.vue'),
      meta: {
        title: 'Dashboard',
        requiresAuth: true,
      },
    },



    /** ============================= Administrator Routes ===============================================================*/
    {
      path: '/admin-dashboard',
      name: 'AdminDashboard',
      component: () => import('../views/Admin/AdminDashboard.vue'),
      meta: {
        title: 'Admin Dashboard',
        requiresAuth: true,
      },
      beforeEnter: adminGuard()
    },
    {
      path: '/aprroval-config',
      name: 'ApprovalConfiguration',
      component: () => import('../views/Admin/ApprovalConfiguration.vue'),
      meta: {
        title: 'Admin - Approval Config',
        requiresAuth: true,
      },
      beforeEnter: adminGuard()
    },
    {
      path: '/roles',
      name: 'RolesMain',
      component: () => import('../views/Admin/RolesMain.vue'),
      meta: {
        title: 'Roles & Permissions',
        requiresAuth: true,
      },
      beforeEnter: adminGuard()
    },
    {
      path: '/checklist',
      name: 'ChecklistItem',
      component: () => import('../views/Admin/ChecklistItem.vue'),
      meta: {
        title: 'Standard Checklist Item',
        requiresAuth: true,
      },
      beforeEnter: adminGuard()
    },
    {
      path: '/set-standard',
      name: 'SetStandardActions',
      component: () => import('../views/Admin/SetStandardActions.vue'),
      meta: {
        title: 'Set Standard Actions',
        requiresAuth: true,
      },
      beforeEnter: adminGuard()
    },
    {
      path: '/logs', // =========== Service Logs and Actions
      name: 'ServiceLogs',
      component: () => import('../views/ServiceLogs.vue'),
      meta: {
        title: 'Action Logs'
      },
      beforeEnter: adminGuard()
    },


    /** Master Data */
    {
      path: '/master-data',
      name: 'MasterData',
      component: () => import('../views/MasterData/MasterData.vue'),
      meta: {
        title: 'Master Data',
        requiresAuth: true,
      },
      beforeEnter: canAccess('read', MD)
    },
    {
      path: '/create-master-data',
      name: 'CreateMasterData',
      component: () => import('../views/MasterData/CreateMasterData.vue'),
      meta: {
        title: 'Master Data',
        requiresAuth: true,
      },
      beforeEnter: canAccess('create', MD)
    },
    {
      path: '/edit-master-data/:id',
      name: 'EditMasterData',
      component: () => import('../views/MasterData/EditMasterData.vue'),
      meta: {
        title: 'Master Data',
        requiresAuth: true,
      },
      beforeEnter: canAccess('edit', MD)
    },
    {
      path: '/view-master-data/:id',
      name: 'ViewMasterData',
      component: () => import('../views/MasterData/ViewMasterData.vue'),
      meta: {
        title: 'Master Data',
        requiresAuth: true,
      },
      beforeEnter: canAccess('read', MD)
    },




    /** Equipment Handling */
    {
      path: '/equipment-handling',
      name: 'EquipmentHandling',
      component: () => import('../views/EquipmentHandling/EquipmentHandling.vue'),
      meta: {
        title: 'Equipment Handling',
        requiresAuth: true
      },
      beforeEnter: canAccess('read', EH),
    },
    {
      path: '/work-order',
      name: 'WorkOrder',
      component: () => import('../views/EquipmentHandling/WorkOrder.vue'),
      meta: {
        title: 'Work Order',
        requiresAuth: true
      },
      beforeEnter: canAccess('create', EH),
    },
    {
      path: '/work-order/:id',
      name: 'WorkOrderApprover',
      component: () => import('../views/EquipmentHandling/WorkOrderApprover.vue'),
      props: true,
      meta: {
        title: 'Work Order',
        requiresAuth: true
      },
      beforeEnter: workOrderMainRequest(['read', 'approve', 'installer', 'delegate'], EH),
    },
    {
      path: '/work-order-installation/:id',
      name: 'WorkOrderInstallation',
      component: () => import('../views/EquipmentHandling/WorkOrderInstallation.vue'),
      props: true,
      meta: {
        title: 'Work Order',
        requiresAuth: true
      },
      beforeEnter: workOrderMainRequest(['installer', 'delegate'], EH),
    },





    /** ========================= Pull Out Request Routes ===================================== */
    {
      path: '/pull-out-request',
      name: 'PullOut',
      component : () => import('../views/PulloutRequest/PulloutRequest.vue'),
      meta: {
        title: 'Pullout Request',
        requiresAuth: true
      },
      beforeEnter: canAccess('read', PR)
    },
    {
      path: '/pull-out-request-form',
      name: 'PullOutRequestForm',
      component : () => import('../views/PulloutRequest/PullOutRequestForm.vue'),
      meta: {
        title: 'Pullout Request',
        requiresAuth: true
      },
      beforeEnter: canAccess('create', PR)
    },
    {
      path: '/pull-out-request-view/:id',
      name: 'PullOutRequestView',
      component : () => import('../views/PulloutRequest/PulloutRequestApproval.vue'),
      meta: {
        title: 'Pullout Request',
        requiresAuth: true
      },
      beforeEnter: canAccess('read', PR)
    },


    /** ========================================== Internal Servicing Routes =================================================== */
    {
      path: '/internal-servicing',
      name: 'InternalServicing',
      component: () => import('../views/InternalServicing/InternalRequestMainData.vue'),
      props: true,
      meta: {
        title: 'Internal Servicing',
        requiresAuth: true
      },
      beforeEnter: internalServicingRequest(['read'], IS)
    },

    {
      path: '/internal-servicing-process/:id',
      name: 'InternalServicingProcess',
      component: () => import('../views/InternalServicing/InternalRequestProcess.vue'),
      props: true,
      meta: {
        title: 'Internal Servicing',
        requiresAuth: true
      },
      beforeEnter: internalServicingRequest(['read','installer'], IS)
    },



    /**
     * ==================================== Preventive Maintenance Routes ========================================
     */
    {
      path: '/preventive-maintenance',
      name: 'PreventiveMaintenance',
      component: () => import('../views/PreventiveMaintenance/PreventiveMaintenance.vue'),
      props: true,
      meta: {
        title: 'Preventive Maintenance',
        requiresAuth: true
      },
      beforeEnter: canAccess('read', PM)
    },

    {
      path: '/create-request/:work_type',
      name: 'CreatePM',
      component: () => import('../views/PreventiveMaintenance/CreatePM.vue'),
      props: true,
      meta: {
        title: 'Preventive Maintenance',
        requiresAuth: true
      },
      beforeEnter: canAccess('create', PM)
    },


    {
      path: '/pm-view/:id/:work_type',
      name: 'PMView',
      component: () => import('../views/PreventiveMaintenance/PMView.vue'),
      props: true,
      meta: {
        title: 'Preventive Maintenance',
        requiresAuth: true
      },
      beforeEnter : preventiveMaintenanceRequest(['read','installer','delegate'], PM)
    },


    /**
     * ==================================== Corrective Maintenance Routes ========================================
     */
    {
      path: '/corrective-maintenance',
      name: 'CorrectiveMaintenance',
      component: () => import('../views/Corrective/Corrective.vue'),
      props: true,
      meta: {
        title: 'Corrective Maintenance',
        requiresAuth: true
      }
    },








    /** ======================= ERRORS REDIRECTION +++++++++++++++++++++++++++++++++++ */

    {
      path: '/forbidden',
      name: 'forbidden',
      component: () => import('../views/Errors/Forbidden.vue'),
      meta: {
        title: 'Forbidden'
      },
    },

  ]
})


router.beforeEach((to, from, next) => {
  const authenticated = JSON.parse(localStorage.getItem('isAuthenticated'))


  const title = to.meta.title || 'Service App'
  document.title = title

  if (to.meta.requiresAuth && !authenticated) {
    localStorage.clear()
    next({ path: '/' })
  } else if (to.path === '/' && authenticated) {
    next({ path: '/dashboard' })
  } else {
    next();
  }

  return false
})

export default router
