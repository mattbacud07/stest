import { createRouter, createWebHistory } from 'vue-router'
import Dashboard from '../views/Dashboard.vue'
import Login from '../views/Login.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'login',
      // component: () => import('../views/Login.vue'),
      component: Login,
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



    /** ============================= Administrator Routes ===============================================================*/
    {
      path: '/admin-dashboard',
      name: 'AdminDashboard',
      component: () => import('../views/Admin/AdminDashboard.vue'),
      meta: {
        requiresAuth: true
      }
    },
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
      name: 'RolesMain',
      component: () => import('../views/Admin/RolesMain.vue'),
      meta: {
        requiresAuth: true
      }
    },
    {
      path: '/pm-settings',
      name: 'PMSettings',
      component: () => import('../views/Admin/PMSettings.vue'),
      meta: {
        requiresAuth: true
      }
    },
    {
      path: '/set-standard',
      name: 'SetStandardActions',
      component: () => import('../views/Admin/SetStandardActions.vue'),
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
      },
    },
    {
      path: '/work-order',
      name: 'WorkOrder',
      component: () => import('../views/EquipmentHandling/WorkOrder.vue'),
      meta: {
        requiresAuth: true
      },
    },
    {
      path: '/view-work-order/:id',
      name: 'ViewWorkOrder',
      component: () => import('../views/EquipmentHandling/ViewWorkOrder.vue'),
      props: true,
      meta: {
        requiresAuth: true
      }
    },
    {
      path: '/approver-work-order/:id',
      name: 'WorkOrderApprover',
      component: () => import('../views/EquipmentHandling/WorkOrderApprover.vue'),
      props: true,
      meta: {
        requiresAuth: true
      }
    },





    /** =======================================Team Leader Routes ========================================================*/
    {
      path: '/tl-dashboard',
      name: 'DashboardTL',
      component: () => import('../views/TeamLeader/DashboardTL.vue'),
      props: true,
      meta: {
        requiresAuth: true
      }
    },


    /** ========================================== Internal Servicing Routes =================================================== */
    {
      path: '/internal-servicing',
      name: 'InternalServicing',
      component: () => import('../views/InternalServicing/InternalRequestMainData.vue'),
      props: true,
      meta: {
        requiresAuth: true
      }
    },

    {
      path: '/internal-servicing-process/:id/:service_id',
      name: 'InternalServicingProcess',
      component: () => import('../views/InternalServicing/InternalRequestProcess.vue'),
      props: true,
      meta: {
        requiresAuth: true
      }
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
        requiresAuth: true
      }
    },


    {
      path: '/pm-view/:id/:work_type',
      name: 'PMView',
      component: () => import('../views/PreventiveMaintenance/PMView.vue'),
      props: true,
      meta: {
        requiresAuth: true
      },
      beforeEnter: (to, from, next) => {
        const validWorkType = ['PM', 'CM']
        const work_type = to.params.work_type

        if (validWorkType.includes(work_type)) next()
        else next(false)
      },
      children: [
        {
          path: 'print-preview',
          name: 'PMPrint',
          component: () => import('../components/Print/PMPrint.vue'),
        }
      ],
    },


    /**
     * ==================================== Corrective Maintenance Routes ========================================
     */
    {
      path: '/corrective-maintenance',
      name: 'Corrective',
      component: () => import('../views/Corrective/Corrective.vue'),
      props: true,
      meta: {
        requiresAuth: true
      }
    },


    // {
    //   path: '/pm-view/:id',
    //   name: 'PMView',
    //   component: () => import('../views/PreventiveMaintenance/PMView.vue'),
    //   props: true,
    //   meta: {
    //     requiresAuth: true
    //   },
    // },

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

  return false
})

export default router
