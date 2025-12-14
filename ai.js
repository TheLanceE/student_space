// deepseek-api.js (frontend JavaScript)
import OpenAI from 'openai';

// Get configuration from your backend (you need to expose it via an API endpoint)
async function getAIConfig() {
    try {
        const response = await fetch('/api/ai-config'); // Create this endpoint
        return await response.json();
    } catch (error) {
        console.error('Failed to load AI config:', error);
        return null;
    }
}

// Or if you want to initialize directly in PHP-rendered HTML:
async function initializeDeepSeek() {
    // If you're embedding config in HTML (from PHP)
    const config = window.aiConfig || await getAIConfig();
    
    if (!config || !config.apiKey) {
        console.error('API key not configured');
        return null;
    }
    
    const openai = new OpenAI({
        baseURL: config.baseURL || "https://api.deepseek.com", // DeepSeek endpoint
        apiKey: config.apiKey,
        dangerouslyAllowBrowser: true, // Only if running in browser
        defaultHeaders: {
            "HTTP-Referer": window.location.origin, // Your actual site URL
            "X-Title": "Your Quiz Application", // Your site name
        }
    });
    
    return openai;
}

// Example usage
async function askDeepSeek(prompt) {
    try {
        const openai = await initializeDeepSeek();
        if (!openai) return "AI service not available";
        
        const completion = await openai.chat.completions.create({
            model: "deepseek-chat", // or "deepseek-coder" for coding tasks
            messages: [
                { role: "user", content: prompt }
            ],
            temperature: 0.7,
            max_tokens: 500
        });
        
        return completion.choices[0].message.content;
    } catch (error) {
        console.error('DeepSeek API error:', error);
        return "Error: " + error.message;
    }
}

export { initializeDeepSeek, askDeepSeek };