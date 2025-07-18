/** When your routing table is too long, you can split it into small modules**/
import Layout from '@/layout/Layout.vue'

const adminRoutes = {
  path: '/cities',
  component: Layout,
  redirect: '/cities/list',
  name: 'Cities',
  alwaysShow: true,
  meta: {
    title: 'cities',
    bootstrapIcon: 'person-workspace',
    permissions: ['view menu cities'],
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
      component: () => import('@/views/cities/List.vue'),
      name: 'CitiesList',
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
