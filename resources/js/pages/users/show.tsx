import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';

interface User {
  id: number;
  first_name: string;
  last_name: string;
  email: string;
  email_verified_at: string | null;
  status: 'active' | 'inactive';
  created_at: string;
  updated_at: string;
}

interface Props {
  user: User;
}

export default function Show({ user }: Props) {
  return (
    <AppLayout>
      <Head title={`${user.first_name} ${user.last_name}`} />
      <div className="py-12">
        <div className="max-w-2xl mx-auto sm:px-6 lg:px-8">
          <div className="flex items-center justify-between mb-6">
            <div>
              <h1 className="text-3xl font-bold tracking-tight">
                {user.first_name} {user.last_name}
              </h1>
              <p className="text-gray-500 mt-1">User profile and details</p>
            </div>
            <Link href="/users">
              <button className="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                Back to Users
              </button>
            </Link>
          </div>

          <div className="bg-white rounded-lg border border-gray-200 p-6">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              {/* First Name */}
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  First Name
                </label>
                <p className="text-gray-900">{user.first_name}</p>
              </div>

              {/* Last Name */}
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Last Name
                </label>
                <p className="text-gray-900">{user.last_name}</p>
              </div>

              {/* Email */}
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Email
                </label>
                <p className="text-gray-900">{user.email}</p>
              </div>

              {/* Status */}
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Status
                </label>
                <span
                  className={`px-3 py-1 rounded-full text-xs font-semibold inline-block ${
                    user.status === 'active'
                      ? 'bg-blue-100 text-blue-800'
                      : 'bg-red-100 text-red-800'
                  }`}
                >
                  {user.status}
                </span>
              </div>

              {/* Email Verified */}
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Email Verified
                </label>
                <span
                  className={`px-3 py-1 rounded-full text-xs font-semibold inline-block ${
                    user.email_verified_at
                      ? 'bg-green-100 text-green-800'
                      : 'bg-gray-100 text-gray-800'
                  }`}
                >
                  {user.email_verified_at ? 'Verified' : 'Not Verified'}
                </span>
              </div>

              {/* Created At */}
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Created At
                </label>
                <p className="text-gray-900">
                  {new Date(user.created_at).toLocaleString()}
                </p>
              </div>

              {/* Updated At */}
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Last Updated
                </label>
                <p className="text-gray-900">
                  {new Date(user.updated_at).toLocaleString()}
                </p>
              </div>
            </div>

            {/* Action Buttons */}
            <div className="flex gap-4 mt-8 pt-6 border-t border-gray-200">
              <Link href={`/users/${user.id}/edit`}>
                <button className="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                  Edit User
                </button>
              </Link>
              <Link href="/users">
                <button className="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                  Back to Users
                </button>
              </Link>
            </div>
          </div>
        </div>
      </div>
    </AppLayout>
  );
}
