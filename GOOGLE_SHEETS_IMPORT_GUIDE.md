# Google Sheets Import Guide for Prayer Times

This guide explains how to use the Google Sheets import feature to automatically import prayer times into your timetable application.

## Prerequisites

1. A Google account
2. A Google Sheet with prayer times data
3. The Google Sheet must be publicly accessible

## Setting Up Your Google Sheet

### 1. Create a New Google Sheet

1. Go to [Google Sheets](https://sheets.google.com)
2. Create a new spreadsheet
3. Name it something like "Prayer Times 2024"

### 2. Set Up the Header Row

In the first row (Row 1), add these column headers in order:

| Date | Fajr Beginning | Fajr Jamaat | Sunrise | Zuhr Beginning | Zuhr Jamaat | Asr Beginning | Asr Jamaat | Maghrib Beginning | Maghrib Jamaat | Isha Beginning | Isha Jamaat | Jumma 1 | Jumma 2 | hijri_date |
|------|----------------|-------------|---------|----------------|-------------|---------------|------------|-------------------|---------------|----------------|-------------|---------|---------|------------|

### 3. Add Your Prayer Times Data

Starting from Row 2, add your prayer times data. Here's an example:

| Date       | Fajr Beginning | Fajr Jamaat | Sunrise | Zuhr Beginning | Zuhr Jamaat | Asr Beginning | Asr Jamaat | Maghrib Beginning | Maghrib Jamaat | Isha Beginning | Isha Jamaat | Jumma 1 | Jumma 2 | hijri_date |
|------------|----------------|-------------|---------|----------------|-------------|---------------|------------|-------------------|---------------|----------------|-------------|---------|---------|------------|
| 2024-01-01 | 05:30          | 05:45       | 06:45   | 12:15          | 12:30       | 15:45         | 16:00      | 18:20             | 18:25          | 19:50          | 20:05       | 12:30   | 13:30   | 15 Jumada al-Awwal 1445 |
| 2024-01-02 | 05:29          | 05:44       | 06:46   | 12:15          | 12:30       | 15:46         | 16:01      | 18:21             | 18:26          | 19:51          | 20:06       | 12:30   | 13:30   | 16 Jumada al-Awwal 1445 |
| 2024-01-03 | 05:28          | 05:43       | 06:47   | 12:16          | 12:31       | 15:47         | 16:02      | 18:22             | 18:27          | 19:52          | 20:07       | 12:30   | 13:30   | 17 Jumada al-Awwal 1445 |

**Important Notes:**
- The system will automatically use the "Beginning" times for each prayer
- The "Jamaat" times are ignored but won't cause errors
- The "hijri_date" column is also ignored but won't cause errors
- Sunrise, Jumma 1, and Jumma 2 are optional fields

### 4. Choose Your Import Method

#### Option A: File Upload (Recommended)
1. In your Google Sheet, go to File → Download
2. Select "Comma-separated values (.csv)"
3. Save the file to your computer
4. Use the file upload option in the import form

#### Option B: URL Import
1. Click the "Share" button in the top-right corner
2. Click "Change to anyone with the link"
3. Set permission to "Viewer"
4. Copy the link

## Supported Date Formats

The import feature supports various date formats:

- `YYYY-MM-DD` (2024-01-01)
- `MM/DD/YYYY` (01/01/2024)
- `DD/MM/YYYY` (01/01/2024)
- `MM-DD-YYYY` (01-01-2024)
- `DD-MM-YYYY` (01-01-2024)

## Supported Time Formats

The import feature supports various time formats:

- `HH:MM` (05:30)
- `HH:MM:SS` (05:30:00)
- `12:00 PM` (12:00 PM)
- `12:00:00 PM` (12:00:00 PM)
- `h:mm A` (5:30 AM)

## Optional Fields

The following fields are optional and can be left empty:
- Sun Rise
- Jumah 1
- Jumah 2

## Importing the Data

### 1. Access the Import Feature

1. Log in to your admin panel
2. Go to "Prayer Times" section
3. Click "Import from Google Sheets"

### 2. Choose Import Method and Enter Data

#### For File Upload (Recommended):
1. Select "Upload Google Sheets File"
2. Click "Choose File" and select your downloaded CSV file
3. Choose whether to overwrite existing entries

#### For URL Import:
1. Select "Import from Google Sheets URL"
2. Paste your Google Sheets URL in the "Google Sheets URL" field
3. Optionally specify a data range (default: A:Z)
4. Choose whether to overwrite existing entries

### 3. Preview the Data (Recommended)

1. Click "Preview Data" to see what will be imported
2. Review the data for any errors
3. Check which entries already exist in your database

### 4. Import the Data

1. Click "Import Prayer Times" to complete the import
2. Review the import results and any error messages

## Troubleshooting

### Common Issues

1. **403 Forbidden Error**
   - Make sure your Google Sheet is publicly accessible
   - Check that the sharing settings allow "Anyone with the link" to view

2. **No Data Found**
   - Verify that your sheet has data in the correct format
   - Check that the header row is in the first row
   - Ensure there are no empty rows at the beginning

3. **Invalid Date/Time Format**
   - Use one of the supported date/time formats listed above
   - Avoid special characters or text in date/time fields

4. **Import Errors**
   - Check the error messages for specific row issues
   - Verify that required fields (Date, Fajr, Zohar, Asr, Maghrib, Isha) are not empty

### Tips for Success

1. **Test with a Small Dataset First**
   - Start with 5-10 rows to test the import
   - Verify the format works before importing large datasets

2. **Use Consistent Formatting**
   - Keep the same date and time format throughout your sheet
   - Avoid mixing different formats in the same column

3. **Check for Duplicates**
   - The system will show you which dates already exist
   - Choose whether to overwrite or skip existing entries

4. **Backup Your Data**
   - Always backup your existing prayer times before importing
   - Test the import in a development environment first

## Sample Google Sheet Template

You can use this template as a starting point:

```
Date,Fajr Beginning,Fajr Jamaat,Sunrise,Zuhr Beginning,Zuhr Jamaat,Asr Beginning,Asr Jamaat,Maghrib Beginning,Maghrib Jamaat,Isha Beginning,Isha Jamaat,Jumma 1,Jumma 2,hijri_date
2024-01-01,05:30,05:45,06:45,12:15,12:30,15:45,16:00,18:20,18:25,19:50,20:05,12:30,13:30,15 Jumada al-Awwal 1445
2024-01-02,05:29,05:44,06:46,12:15,12:30,15:46,16:01,18:21,18:26,19:51,20:06,12:30,13:30,16 Jumada al-Awwal 1445
2024-01-03,05:28,05:43,06:47,12:16,12:31,15:47,16:02,18:22,18:27,19:52,20:07,12:30,13:30,17 Jumada al-Awwal 1445
```

## Support

If you encounter any issues with the import feature, please check:

1. The error messages displayed during import
2. The application logs for detailed error information
3. Ensure your Google Sheet follows the required format

For additional support, contact your system administrator.