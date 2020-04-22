using System.Collections.Generic;
using System.Runtime.Serialization;
using System.Security.Claims;
using System.Text.Json.Serialization;
using Apache.Ignite.Core.Cache.Configuration;
using Domain.Security;

namespace Domain
{
    public class Role
    {
        public Role()
        {
            
        }
        
        [QuerySqlField(IsIndexed = true)]
        public long Id { get; set; }
        [QuerySqlField(IsIndexed = true)]
        public string Name { get; set; }
        
        
        [QuerySqlField]
        public List<BaseClaim> Claims { get; set; }

        public bool ShouldSerializeClaims()
        {
            return false;
        }
    }
    
}