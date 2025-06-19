/** When your routing table is too long, you can split it into small modules**/
import Layout from '@/layout/Layout.vue'

const adminRoutes = {
  path: '/pages',
  component: Layout,
  redirect: '/pages/users',
  name: 'Pages',
  alwaysShow: true,
  meta: {
    title: 'pages',
    bootstrapIcon: 'person-workspace',
    permissions: ['view menu pages'],
  },
  children: [
    /** User managements */
    {
      path: 'users/edit/:id(\\d+)',
      component: () => import('@/views/users/UserProfile.vue'),
      name: 'UserProfile',
      meta: { title: 'userProfile', noCache: true, permissions: ['manage user'] },
      hidden: true,
    },
    {
      path: 'list',
      component: () => import('@/views/pages/List.vue'),
      name: 'PagesList',
      meta: {title: 'list', bootstrapIcon: 'people', permissions: ['manage user']},
    },
    /** Role and permission */
    {
      path: 'roles',
      component: () => import('@/views/role-permission/List.vue'),
      name: 'RoleList',
      meta: {title: 'rolePermission', bootstrapIcon: 'person-lines-fill', permissions: ['manage permission']},
      hidden: true,
    },
  ],
}

export default adminRoutes
