<?php
namespace Core\Packages\User\Bookmark;
// Class accessed from the crontroller with requests such as /user/{username}/bookmarks/
// in which the User object will instantiate this object and look for details.
// User is not the only "object" who can call Bookmark.  Consider a "site" object wanting to access
// and manage its bookmark titles at will..
class Bookmark {
}
?>