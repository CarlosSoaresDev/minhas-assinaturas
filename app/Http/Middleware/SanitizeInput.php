<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SanitizeInput
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Skip sanitation for Livewire requests to prevent snapshot corruption
        if ($request->hasHeader('X-Livewire')) {
            return $next($request);
        }

        $input = $request->all();

        array_walk_recursive($input, function (&$value, $key) {
            if (is_string($value) && !in_array(strtolower($key), ['password', 'password_confirmation', 'signature'])) {
                // Remove HTML tags to prevent XSS
                $value = strip_tags($value);
                
                // Enforce max length of 255 for generic strings if they are too long and not textareas
                // Note: For textareas, we allow more, but we assume basic fields here.
                // We'll just strip tags globally here. 
                // Actual length validation should ideally be done in Form Requests / Livewire validation,
                // but we add a hard cap for standard inputs here just in case.
                if (strlen($value) > 5000) {
                    $value = substr($value, 0, 5000);
                }
            }
        });

        $request->merge($input);

        return $next($request);
    }
}
