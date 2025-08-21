<div class="bg-white rounded-lg shadow-sm p-4">
    <div class="text-sm text-gray-600 mb-3 font-medium">Recent Reports</div>
    <div class="overflow-x-auto">
        <table class="min-w-full text-left">
            <thead class="text-xs text-gray-500">
                <tr>
                    <th class="pb-2">ID</th>
                    <th class="pb-2">Title</th>
                    <th class="pb-2">Status</th>
                    <th class="pb-2">Date</th>
                    <th class="pb-2">Created</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reports as $r)
                    <tr class="border-t">
                        <td class="py-2">{{ $r->id }}</td>
                        <td class="py-2">{{ $r->title }}</td>
                        <td class="py-2">{{ $r->status ?? '-' }}</td>
                        <td class="py-2">{{ optional($r->report_date)->format('M d, Y') ?? '-' }}</td>
                        <td class="py-2">{{ optional($r->created_at)->format('M d, Y H:i') ?? '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="py-4 text-sm text-gray-500">No reports yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>