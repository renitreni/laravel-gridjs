<div>
    <div id="{{ $name ?? 'wrapper' }}"></div>
</div>

@push('initialized')
    <script>
        let {{ $name ?? 'wrapper' }} = new gridjs.Grid({
            columns: [@foreach($table->getColumns() as $column)
                @if(is_array($column))
            {
                @isset($column['sort']) sort: @json($column['sort']), @endisset
                @isset($column['name']) name: '{{ $column['name'] }}', @endisset
                @isset($column['formatter'])
                formatter: (_, row) => gridjs.html(_),
                @endisset
            }
                @else
                    '{{ $column }}'
                @endif,
                @endforeach
            ],
            pagination: {
                enabled: true,
                limit: 5,
                server: {
                    url: function(prev, page, limit) {
                        let $link = `${prev}&limit=${limit}&offset=${page * limit}`;
                        @if($table->getTargetForm())
                            const formData = new FormData(document.querySelector('{{$table->getTargetForm()}}'))
                            for (var pair of formData.entries()) {
                                console.log(pair[0] + ': ' + pair[1]);
                                $link += `&${pair[0]}=${pair[1]}`
                            }
                        @endif
                        return $link;
                    }
                }
            },
            @if($table->isFixedHeader())
            fixedHeader: true,
            @endif
            @if($table->searchStatus())
                search: {
                    server: {
                        url: (prev, keyword) => `${prev}&search=${keyword}`
                    }
                },
            @endif
            sort: {
                multiColumn: false,
                server: {
                    url: (prev, columns) => {
                        if (!columns.length) return prev;

                        const col = columns[0];
                        const dir = col.direction === 1 ? 'asc' : 'desc';
                        let colName = [@foreach($table->getColumns() as $key => $column) '{{ $key }}', @endforeach][col.index];

                        return `${prev}&order=${colName}&dir=${dir}`;
                    }
                }
            },
            server: {
                url: '{{ $table->getRoute() }}?',
                method: 'GET',
                then: data => data.data.map(data => [@foreach($table->getColumns() as $key => $column) data.{{ $key }}, @endforeach]),
                total: data => data.total,
            }
        }).render(document.getElementById("{{ $name ?? 'wrapper' }}"));
    </script>
@endpush
