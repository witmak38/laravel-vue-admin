/** When your routing table is too long, you can split it into small modules**/
import Layout from '@/layout/Layout.vue'

const adminRoutes = {
  path: '/staff',
  component: Layout,
  redirect: '/staff/users',
  name: 'Staff',
  alwaysShow: true,
  meta: {
    title: 'staff',
    bootstrapIcon: 'person-workspace',
    permissions: ['view menu staff'],
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
      component: () => import('@/views/staff/List.vue'),
      name: 'StaffList',
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
