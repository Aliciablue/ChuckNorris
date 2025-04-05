<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('results.search_results') }}</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            /* Or another common, readable font like Helvetica, sans-serif */
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }

        h1 {
            color: #007bff;
            /* A common primary color */
            margin-bottom: 20px;
        }

        p {
            margin-bottom: 15px;
        }

        ul {
            list-style-type: disc;
            padding-left: 20px;
            margin-bottom: 20px;
        }

        li {
            margin-bottom: 5px;
        }

        .button {
            display: inline-block;
            padding: 10px 20px;
            font-size: 16px;
            text-align: center;
            text-decoration: none;
            cursor: pointer;
            border: none;
            border-radius: 5px;
            background-color: #007bff;
            color: white;
            margin-top: 20px;
        }

        .button:hover {
            background-color: #0056b3;
        }
    </style>
</head>

<body>
    <div style="background-color: #fff; padding: 30px; border-radius: 5px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);">
        <h1 style="color: #007bff; margin-bottom: 20px;">{{ __('results.search_results') }}</h1>
        @if (isset($total) && $total > 0)
            <p>{{ __('message.total_results') }}: {{ $total }}</p>
            <p>{{ __('messages.showing_results_message', ['displayed' => count($results), 'total' => $total]) }}</p>
        @endif

        @if ($searchType === 'keyword' && $searchTerm)
            <p style="margin-bottom: 15px;">{{ __('results.keyword_search') }} <strong
                    style="font-weight: bold;">{{ $searchTerm }}</strong></p>
        @elseif ($searchType === 'category' && $searchTerm)
            <p style="margin-bottom: 15px;">{{ __('results.category_search') }} <strong
                    style="font-weight: bold;">{{ $searchTerm }}</strong></p>
        @elseif ($searchType === 'random')
            <p style="margin-bottom: 15px;">{{ __('results.random_search') }}</p>
        @endif

        @if (count($results) > 0)
            <ul style="list-style-type: disc; padding-left: 20px; margin-bottom: 20px;">
                @foreach ($results as $result)
                    <li style="margin-bottom: 5px;">
                        {{ $result['value'] ?? (__('results.no_results_singular')) }}</li>
                @endforeach
            </ul>
        @else
            <p style="margin-bottom: 15px;">{{ __('results.no_results') }}</p>
        @endif

        <p style="margin-top: 20px;"><a href="{{ $allResultsUrl }}"
                style="display: inline-block; padding: 10px 20px; font-size: 16px; text-align: center; text-decoration: none; cursor: pointer; border: none; border-radius: 5px; background-color: #007bff; color: white;"
                onmouseover="this.style.backgroundColor='#0056b3'"
                onmouseout="this.style.backgroundColor='#007bff'">{{ __('messages.view_all_results') }}</a></p>

        <p style="margin-top: 30px;">{{ __('messages.thank_you') }}</p>
    </div>
</body>

</html>
