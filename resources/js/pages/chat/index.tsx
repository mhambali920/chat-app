import { UserInfo } from '@/components/user-info';
import AppLayout from '@/layouts/app-layout';
import { User, type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Chat',
        href: '/chat',
    },
];

function Index({ users, conversations }: { users: User[]; conversations: any }) {
    const { data, setData, post, processing, errors } = useForm<{ name: string; user_ids: number[] }>({
        name: '',
        user_ids: [],
    });

    const handleCreateConversation = (userId: number) => {
        setData('user_ids', [userId]);

        post(route('chat.store.conversation'), {
            preserveScroll: true,
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Chat" />
            <div className="flex h-full flex-1 gap-4 rounded-xl p-4">
                {/* Sidebar Kontak & Buat Percakapan */}
                <div className="bg-secondary w-72 rounded p-4">
                    <div className="mb-4">
                        <label className="mb-1 block font-semibold">Judul Percakapan</label>
                        <input
                            type="text"
                            value={data.name}
                            onChange={(e) => setData('name', e.target.value)}
                            placeholder="Contoh: Diskusi Project"
                            className="w-full rounded border px-3 py-2 focus:ring focus:outline-none"
                        />
                        {errors.name && <p className="mt-1 text-sm text-red-500">{errors.name}</p>}
                        {errors.user_ids && <p className="mt-2 text-sm text-red-500">{errors.user_ids}</p>}
                    </div>
                    <div className="mb-2 font-semibold">Kontak</div>
                    <div className="space-y-2">
                        {users.map((user) => (
                            <div
                                key={user.id}
                                onClick={() => handleCreateConversation(user.id)}
                                className="hover:bg-card flex cursor-pointer items-center gap-2 rounded p-2 transition-colors duration-200"
                            >
                                <UserInfo user={user} />
                            </div>
                        ))}
                    </div>
                </div>

                {/* Daftar Percakapan */}
                <div className="bg-secondary flex-1 overflow-y-auto rounded p-4">
                    <h2 className="mb-4 text-lg font-semibold">Daftar Percakapan</h2>
                    {conversations.length === 0 ? (
                        <div className="text-gray-400">Belum ada percakapan.</div>
                    ) : (
                        <div className="space-y-3">
                            {conversations.map((conv) => (
                                <Link
                                    key={conv.id}
                                    href={route('chat.show', conv.id)}
                                    className="bg-card hover:bg-accent block rounded p-3 transition"
                                >
                                    <div className="font-medium">{conv.name}</div>
                                    <div className="text-muted-foreground text-sm">Anggota: {conv.users.map((u) => u.name).join(', ')}</div>
                                </Link>
                            ))}
                        </div>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}

export default Index;
