# acl.auth.php
# <?php exit()?>
# Don't modify the lines above
#
# Access Control Lists
#
# Editing this file by hand shouldn't be necessary. Use the ACL
# Manager interface instead.
#
# If your auth backend allows special char like spaces in groups
# or user names you need to urlencode them (only chars <128, leave
# UTF-8 multibyte chars as is)
#
# none   0
# read   1
# edit   2
# create 4
# upload 8
# delete 16

*               @ALL        8
private:*       @ALL        0

# for testing wildcards:
users:*            @ALL         1
users:%USER%:*     %USER%       16
groups:*           @ALL         1
groups:%GROUP%:*   %GROUP%      16
