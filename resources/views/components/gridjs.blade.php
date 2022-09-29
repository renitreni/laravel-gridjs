<div>
    <div id="{{ $name ?? 'wrapper' }}"></div>
</div>

@push('initialized')
    <script>
        let build{{ $name }} = JSON.parse('{!! $table !!}');

        // My Custom fix for GridJs Laravel only
        build{{ $name }}.server.body = JSON.stringify(build{{ $name }}.server.body)
        build{{ $name }}.server.total = data => data.total;
        build{{ $name }}.server.then = data => data.data.map(function (item) {
            let hold = [];
            build{{ $name }}.mapped.forEach(function (value) {
                hold.push(gridjs.html(item[value]));
            });
            return hold;
        });

        // PAGINATION BUILDER W/ FORM SEARCH DATA
        build{{ $name }}.pagination.server.url = function (prev, page, limit) {
            let $link = `${prev}&limit=${limit}&offset=${page * limit}`;
            if (document.querySelector(build{{ $name }}.formTarget)) {
                const formData = new FormData(document.querySelector(build{{ $name }}.formTarget))
                for (var pair of formData.entries()) {
                    $link += `&${pair[0]}=${pair[1]}`
                }
            }
            return $link;
        };

        // SEARCH
        if (build{{ $name }}.search.server) {
            build{{ $name }}.search.server.url = (prev, keyword) => `${prev}&search=${keyword}`;
        } else {
            build{{ $name }}.search = null;
        }

        build{{ $name }}.style = {
            table: {
                'white-space': 'nowrap'
            }
        }

        // SORT
        build{{ $name }}.sort = {
            multiColumn: false,
            server: {
                url: (prev, columns) => {
                    if (!columns.length) return prev;
                    const col = columns[0];
                    const dir = col.direction === 1 ? 'asc' : 'desc';
                    let colName = build{{ $name }}.mapped[col.index];
                    return `${prev}&order=${colName}&dir=${dir}`;
                }
            }
        }

        let {{ $name ?? 'wrapper' }} = new gridjs.Grid(build{{ $name }})
            .render(document.getElementById("{{ $name ?? 'wrapper' }}"));

        window.addEventListener('{{ $name ?? 'wrapper' }}Render', event => {
            {{ $name ?? 'wrapper' }}.forceRender();
        })
    </script>
@endpush
