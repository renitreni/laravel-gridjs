<?php

use Faker\Factory;

$rand = Factory::create()->buildingNumber()
?>
<div>
    <div id="{{ $name ?? 'wrapper' }}"></div>
</div>

@push('initialized')
    <script>
        let build{{ $rand }} = JSON.parse('{!! $table  !!}');
        // My Custom fix for GridJs Laravel only
        build{{ $rand }}.server.body = JSON.stringify(build{{ $rand }}.server.body)
        build{{ $rand }}.server.total = data => data.total;
        build{{ $rand }}.server.then = data => data.data.map(function (item) {
            let hold = [];
            build{{ $rand }}.mapped.forEach(function (value) {
                hold.push(gridjs.html(item[value]));
            });
            return hold;
        });

        build{{ $rand }}.pagination.server.url = function (prev, page, limit) {
            let $link = `${prev}&limit=${limit}&offset=${page * limit}`;
            const formData = new FormData(document.querySelector(build{{ $rand }}.formTarget))
            for (var pair of formData.entries()) {
                $link += `&${pair[0]}=${pair[1]}`
            }
            return $link;
        };
        if (build{{ $rand }}.search.server) {
            build{{ $rand }}.search.server.url = (prev, keyword) => `${prev}&search=${keyword}`;
        } else {
            build{{ $rand }}.search = null;
        }
        build{{ $rand }}.sort = {
            multiColumn: false,
            server: {
                url: (prev, columns) => {
                    if (!columns.length) return prev;
                    const col = columns[0];
                    const dir = col.direction === 1 ? 'asc' : 'desc';
                    let colName = build{{ $rand }}.mapped[col.index];
                    return `${prev}&order=${colName}&dir=${dir}`;
                }
            }
        }

        let {{ $name ?? 'wrapper' }} = new gridjs.Grid(build{{ $rand }})
            .render(document.getElementById("{{ $name ?? 'wrapper' }}"));
    </script>
@endpush
