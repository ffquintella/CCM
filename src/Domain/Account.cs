using System.Runtime.CompilerServices;
using Apache.Ignite.Core.Cache.Configuration;
using System.Text.Json.Serialization;

namespace Domain
{
    public class Account
    {
        [QuerySqlField(IsIndexed = true)]
        public long Id { get; set; }
        
        [QuerySqlField(IsIndexed = true)]
        public string Login { get; set; }
        
        public string Password { internal get; set; }
        
        [QuerySqlField]
        public bool Active { get; set; } = true;

        public string GetPassword()
        {
            return Password;
        }
    }
}