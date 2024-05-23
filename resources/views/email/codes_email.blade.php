<div style="background: #e1d6eb;border-radius: 15px;padding: 15px">
    <h1 style="color: #371e4e">dear {{ $name }}</h1>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <table class="table">
        <thead>
            <tr>
                <th style="background: #e1d6eb">product name</th>
                <th style="background: #e1d6eb">product part title</th>
                <th style="background: #e1d6eb">code</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($content as $item)
                <tr>
                    <td style="color: #371e4e;background: #e1d6eb">{{ $item[1]->name }}</td>
                    <td style="color: #371e4e;background: #e1d6eb">{{ $item[2]->title }}</td>
                    <td style="color: #371e4e;background: #e1d6eb">{{ $item[0]->code }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
