<h2>Recipes:</h2>

@foreach($meals as $meal)
    <div style="border: 1px solid #ddd; padding: 20px; margin-bottom: 20px; border-radius: 10px; display: flex;">
        <div style="flex: 1;">
            <h3>{{ $meal['strMeal'] }}</h3>
            <img src="{{ $meal['strMealThumb'] }}" width="200" style="border-radius: 10px;">
        </div>

        <div style="flex: 2; display: flex; gap: 20px;">
            <div style="background: #e8f5e9; padding: 10px; border-radius: 5px; flex: 1;">
                <h4 style="color: green;">✅ Tev ir:</h4>
                <ul>
                    @foreach($meal['have_ingredients'] as $item)
                        <li>{{ $item }}</li>
                    @endforeach
                </ul>
            </div>

            <div style="background: #ffebee; padding: 10px; border-radius: 5px; flex: 1;">
                <h4 style="color: #c62828;">AI system:</h4>
                <ul>
                    @foreach($meal['missing_ingredients'] as $item)
                        <li><strong>{{ $item }}</strong></li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endforeach