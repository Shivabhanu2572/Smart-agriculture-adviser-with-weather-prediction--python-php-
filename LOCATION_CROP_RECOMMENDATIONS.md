# Location-Based Crop Recommendations System

## Overview
The Smart Agriculture Advisor now includes a real-time location-based crop recommendation system that automatically detects the user's location and provides personalized crop suggestions based on their district and current season.

## Features

### 1. Real-Time Location Detection
- Automatically detects user's location using IP geolocation API (ipapi.co)
- Falls back to default location (Bangalore) if detection fails
- Shows current date and season information

### 2. Dynamic Crop Recommendations
- Fetches crop recommendations from database based on detected district
- Provides season-specific recommendations if exact district match not found
- Includes fallback recommendations for all seasons

### 3. User Interface Enhancements
- Real-time loading indicators
- Location status display with date and season
- Refresh button to manually update location and recommendations
- Error handling with helpful user feedback

## How It Works

### Frontend (dashboard.php)
1. **Location Detection**: Uses `ipapi.co` API to get user's city/district
2. **API Call**: Sends district and current month to `get_crop_recommendations.php`
3. **Display Update**: Shows recommendations with location, date, and season info
4. **Refresh Functionality**: Allows users to manually refresh location data

### Backend (get_crop_recommendations.php)
1. **Input Validation**: Validates district parameter from JSON request
2. **Database Query**: Searches for exact district and month matches
3. **Seasonal Fallback**: If no exact match, provides season-based recommendations
4. **Hardcoded Fallback**: Includes predefined recommendations for all seasons

## Database Requirements

The system requires a `district_crop_recommendation` table with the following structure:
```sql
CREATE TABLE district_crop_recommendation (
    id INT AUTO_INCREMENT PRIMARY KEY,
    district VARCHAR(100),
    month VARCHAR(50),
    crop1 VARCHAR(100),
    crop2 VARCHAR(100),
    crop3 VARCHAR(100),
    summary TEXT
);
```

## API Endpoint

### POST /get_crop_recommendations.php
**Request Body:**
```json
{
    "district": "Mumbai",
    "month": "January"
}
```

**Response:**
```json
{
    "success": true,
    "district": "Mumbai",
    "month": "January",
    "recommendations": [
        {
            "crop1": "Wheat",
            "crop2": "Mustard",
            "crop3": "Peas",
            "summary": "Winter crops suitable for Mumbai region",
            "district": "Mumbai",
            "month": "Winter"
        }
    ],
    "count": 1
}
```

## Season Definitions
- **Summer**: March to May
- **Monsoon**: June to September
- **Post-Monsoon**: October to November
- **Winter**: December to February

## Error Handling
- Database connection failures
- API request failures
- Invalid input parameters
- No matching recommendations found

## User Experience
1. User visits dashboard
2. System automatically detects location
3. Shows loading indicator while fetching recommendations
4. Displays personalized crop suggestions
5. User can refresh to get updated recommendations
6. Fallback recommendations provided if location detection fails

## Technical Notes
- Uses CORS headers for cross-origin requests
- Implements proper error handling and user feedback
- Responsive design for mobile devices
- Real-time weather integration with location
- Graceful degradation when services are unavailable 