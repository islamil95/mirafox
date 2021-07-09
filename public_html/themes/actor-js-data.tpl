{to_js name="actorId" var=$actor->USERID}
{to_js name="actorLogin" var=$actor->username}
{to_js name="actorAvatar" var=$actor->profilepicture}
{to_js name="actorLevel" var=$actor->level}
{to_js name="actorTimezone" var=Timezone::formatString($actor->timezone, "merged")}
{to_js name="actorIsVirtual" var=$actor->isVirtual}