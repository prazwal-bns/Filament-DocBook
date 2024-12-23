<div>
    <table class="w-full border border-collapse border-gray-700 table-auto">
        <thead>
            <tr>
                @foreach ($columns as $column)
                    <th class="px-4 py-2 text-left border border-gray-700">
                        {{ ucfirst($column) }}
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $row)
                <tr>
                    @foreach ($columns as $column)
                        <td class="px-4 py-2 border border-gray-700">
                            {{ $row[$column] ?? '' }}
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
