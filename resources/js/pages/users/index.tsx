import { Head, Link, router } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { useState } from 'react';
import { Trash2, Edit2, Eye } from 'lucide-react';

interface User {
  id: number;
  first_name: string;
  last_name: string;
  email: string;
  email_verified_at: string | null;
  status: 'active' | 'inactive';
  created_at: string;
}

interface Props {
  users: {
    data: User[];
    links: any;
    meta: any;
  };
  filters: {
    search?: string;
    status?: string;
    verified?: string;
  };
}

export default function UsersIndex({ users, filters }: Props) {
  const [search, setSearch] = useState(filters.search || '');
  const [statusFilter, setStatusFilter] = useState(filters.status || '');
  const [verifiedFilter, setVerifiedFilter] = useState(filters.verified || '');

  const handleFilter = () => {
    const params = new URLSearchParams();
    if (search) params.append('search', search);
    if (statusFilter) params.append('status', statusFilter);
    if (verifiedFilter) params.append('verified', verifiedFilter);

    window.location.href = `/users${params.toString() ? '?' + params.toString() : ''}`;
  };

  const handleDelete = (userId: number) => {
    if (confirm('Are you sure you want to delete this user?')) {
      router.delete(`/users/${userId}`);
    }
  };

  return (
    <AppLayout>
      <Head title="Users" />
      <div className="py-12">
        <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
          <div className="flex items-center justify-between mb-6">
            <div>
              <h1 className="text-3xl font-bold tracking-tight">Users</h1>
              <p className="text-gray-500 mt-1">
                Manage users and their access to the system
              </p>
            </div>
            <Link href="/users/create">
              <button className="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                Create User
              </button>
            </Link>
          </div>

          {/* Filters */}
          <div className="bg-white rounded-lg border border-gray-200 p-4 mb-6 space-y-4">
            <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">
                  Search
                </label>
                <input
                  type="text"
                  placeholder="Search by name or email..."
                  value={search}
                  onChange={(e) => setSearch(e.target.value)}
                  onKeyPress={(e) => {
                    if (e.key === 'Enter') handleFilter();
                  }}
                  className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">
                  Status
                </label>
                <select
                  value={statusFilter}
                  onChange={(e) => setStatusFilter(e.target.value)}
                  className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                  <option value="">All Statuses</option>
                  <option value="active">Active</option>
                  <option value="inactive">Inactive</option>
                </select>
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">
                  Verified
                </label>
                <select
                  value={verifiedFilter}
                  onChange={(e) => setVerifiedFilter(e.target.value)}
                  className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                  <option value="">All</option>
                  <option value="yes">Verified</option>
                  <option value="no">Not Verified</option>
                </select>
              </div>
              <div className="flex items-end">
                <button
                  onClick={handleFilter}
                  className="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                >
                  Filter
                </button>
              </div>
            </div>
          </div>

          {/* Users Table */}
          <div className="bg-white rounded-lg border border-gray-200 overflow-hidden">
            {users.data.length === 0 ? (
              <div className="p-8 text-center">
                <p className="text-gray-500">No users found</p>
              </div>
            ) : (
              <>
                <table className="w-full">
                  <thead className="bg-gray-50 border-b border-gray-200">
                    <tr>
                      <th className="px-6 py-3 text-left text-sm font-medium text-gray-700">Name</th>
                      <th className="px-6 py-3 text-left text-sm font-medium text-gray-700">Email</th>
                      <th className="px-6 py-3 text-left text-sm font-medium text-gray-700">Verified</th>
                      <th className="px-6 py-3 text-left text-sm font-medium text-gray-700">Status</th>
                      <th className="px-6 py-3 text-left text-sm font-medium text-gray-700">Created</th>
                      <th className="px-6 py-3 text-left text-sm font-medium text-gray-700">Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    {users.data.map((user) => (
                      <tr key={user.id} className="border-b border-gray-200 hover:bg-gray-50">
                        <td className="px-6 py-4 font-medium">
                          {user.first_name} {user.last_name}
                        </td>
                        <td className="px-6 py-4">{user.email}</td>
                        <td className="px-6 py-4">
                          <span
                            className={`px-3 py-1 rounded-full text-xs font-semibold ${
                              user.email_verified_at
                                ? 'bg-green-100 text-green-800'
                                : 'bg-gray-100 text-gray-800'
                            }`}
                          >
                            {user.email_verified_at ? 'Verified' : 'Not Verified'}
                          </span>
                        </td>
                        <td className="px-6 py-4">
                          <span
                            className={`px-3 py-1 rounded-full text-xs font-semibold ${
                              user.status === 'active'
                                ? 'bg-blue-100 text-blue-800'
                                : 'bg-red-100 text-red-800'
                            }`}
                          >
                            {user.status}
                          </span>
                        </td>
                        <td className="px-6 py-4">
                          {new Date(user.created_at).toLocaleDateString()}
                        </td>
                        <td className="px-6 py-4">
                          <div className="flex gap-2">
                            <Link href={`/users/${user.id}`}>
                              <button
                                className="p-2 text-blue-600 hover:bg-blue-50 rounded-lg"
                                title="View user"
                              >
                                <Eye className="w-4 h-4" />
                              </button>
                            </Link>
                            <Link href={`/users/${user.id}/edit`}>
                              <button
                                className="p-2 text-gray-600 hover:bg-gray-50 rounded-lg"
                                title="Edit user"
                              >
                                <Edit2 className="w-4 h-4" />
                              </button>
                            </Link>
                            <button
                              className="p-2 text-red-600 hover:bg-red-50 rounded-lg"
                              onClick={() => handleDelete(user.id)}
                              title="Delete user"
                            >
                              <Trash2 className="w-4 h-4" />
                            </button>
                          </div>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
                {/* Pagination */}
                <div className="px-6 py-4 border-t border-gray-200 flex justify-center gap-2">
                  {users.links.map((link: any, index: number) => (
                    <Link key={index} href={link.url || '#'}>
                      <button
                        disabled={!link.url}
                        className={`px-3 py-2 rounded-lg ${
                          link.active
                            ? 'bg-blue-600 text-white'
                            : 'bg-gray-100 text-gray-700 hover:bg-gray-200 disabled:opacity-50'
                        }`}
                        dangerouslySetInnerHTML={{ __html: link.label }}
                      />
                    </Link>
                  ))}
                </div>
              </>
            )}
          </div>
        </div>
      </div>
    </AppLayout>
  );
}

