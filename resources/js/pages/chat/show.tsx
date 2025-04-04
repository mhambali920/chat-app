import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { SharedData, type BreadcrumbItem } from '@/types';
import { Head, useForm, usePage } from '@inertiajs/react';
import { format, isToday, isYesterday, parseISO } from 'date-fns';
import { useEffect, useRef, useState } from 'react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Chat',
        href: '/chat',
    },
];

function Show({ conversation, messages: initialMessages }) {
    const { auth } = usePage<SharedData>().props;
    const [messages, setMessages] = useState(initialMessages.data || []);
    const { data, setData, post, processing, reset } = useForm({
        body: '',
        type: 'text',
        file: null,
        latitude: null,
        longitude: null,
    });

    const messagesEndRef = useRef(null);

    function formatChatTimestamp(timestamp: string) {
        const date = parseISO(timestamp);

        if (isToday(date)) {
            return format(date, 'HH:mm');
        }

        if (isYesterday(date)) {
            return `Kemarin ${format(date, 'HH:mm')}`;
        }

        return format(date, 'dd/MM/yyyy HH:mm');
    }

    const handleSendMessage = (e) => {
        e.preventDefault();

        if (!data.body.trim()) return;

        post(route('chat.store', conversation.id), {
            preserveScroll: true,
            onSuccess: () => reset('body'), // Kosongkan input setelah kirim
        });
    };

    useEffect(() => {
        const channel = window.Echo.private(`conversation.${conversation.id}`);

        channel.listen('MessageSent', (event) => {
            console.log(event);
            setMessages((prev) => [...prev, event.message]);
        });

        return () => {
            window.Echo.leave(`conversation.${conversation.id}`);
        };
    }, [conversation.id]);

    // Auto scroll to bottom
    useEffect(() => {
        messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' });
    }, [messages]);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Chat" />
            <div className="flex h-[calc(100vh-100px)] flex-1 flex-col gap-4 rounded-xl p-4">
                {/* Daftar Pesan */}
                <div className="bg-secondary flex-1 overflow-y-auto rounded p-4">
                    <h2 className="mb-4 text-lg font-semibold">Messages</h2>
                    <div>
                        {messages.map((message) => {
                            const isCurrentUser = message.user.id === auth.user.id;
                            const formattedTime = formatChatTimestamp(message.created_at);

                            return (
                                <div key={message.id} className={`flex ${isCurrentUser ? 'justify-end' : 'justify-start'} animate-in my-6`}>
                                    <div className={`max-w-[75%]`}>
                                        {/* Nama Pengirim */}
                                        {!isCurrentUser && (
                                            <span className="text-muted-foreground mb-1 block text-xs font-semibold">{message.user.name}</span>
                                        )}

                                        {/* Bubble Pesan */}
                                        <div
                                            className={`relative inline-block rounded-2xl px-4 py-2 shadow-sm ${
                                                isCurrentUser ? 'bg-primary rounded-br-none text-white' : 'rounded-bl-none bg-gray-200 text-gray-900'
                                            }`}
                                        >
                                            {message.body}
                                            <span className={`absolute -bottom-4 text-[10px] text-gray-500 ${isCurrentUser ? 'right-0' : 'left-0'}`}>
                                                {formattedTime}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            );
                        })}

                        <div ref={messagesEndRef} />
                    </div>
                </div>

                {/* Input Pesan */}
                <form onSubmit={handleSendMessage} className="bg-muted flex items-center gap-2 rounded p-2 shadow">
                    <input
                        type="text"
                        value={data.body}
                        onChange={(e) => setData('body', e.target.value)}
                        placeholder="Tulis pesan..."
                        className="focus:border-primary focus:ring-primary flex-1 rounded border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:ring-1 focus:outline-none"
                    />
                    <Button type="submit" disabled={processing || !data.body.trim()} variant={'default'}>
                        Kirim
                    </Button>
                </form>
            </div>
        </AppLayout>
    );
}

export default Show;
