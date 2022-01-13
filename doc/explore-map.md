# Explore map

Explore map shows actual number of posts. 
For performance reasons, these post counts are not generated on every request but rather pre-generated avery 2 hours using cron. 

To pre-generate manually, run this command
    
    bin/console cocorico:listing:update-count