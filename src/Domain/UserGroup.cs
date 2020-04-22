using System.Collections.Generic;
using Apache.Ignite.Core.Cache.Configuration;
using Domain.Security;

namespace Domain
{
    public class UserGroup
    {
        public UserGroup()
        {
            UsersIds = new List<long>();
            RolesIds = new List<long>();
        }
        
        [QuerySqlField(IsIndexed = true)]
        public long Id { get; set; }
        [QuerySqlField(IsIndexed = true)]
        public string Name { get; set; }

        public List<long> UsersIds { get; set; } 
        
        public List<long> RolesIds { get; set; }
        
    }
}