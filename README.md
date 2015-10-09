# youtube-scrapper
Youtube scrapping sub-mission: targeting whether video is viral, scrapping over trendy videos (using built in keyword set), database manipulation to retervive daily views (+statistics)

# Basic mission for the next couple of days:
1) Change the constructor function in the class to it will call a function to do the main core scrapping. 
2) Change the main core scrapping algorithem so it will use google client library instead of sending HTTP GET requests to the
    web service API.
3) Add db functionality: insert for every object (video object) the following properties: views count, subscribers, likes count, dislikes count, video duration, video tags. 
4) Add db functionality to mark current inserted websites to crawl over in order to determine the daily views count.
5) Add logic functionality to check whether a video is viral or not - if the video is viral the scrapper should add the video to "viral videos" views table.
6) Add trendy keywords to the db (built in list).
7) Crawl over the keywords list and search for 50 videos per keyword.
8) Add time functionality to stop execution of the program after X (minutes) are over.
9) Add shell scripting to add the functionality of script excecution every Y hours.

