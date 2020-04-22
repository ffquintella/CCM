using Apache.Ignite.Core.Cache.Configuration;

namespace Domain
{
    public class User: Person
    {
        public User()
        {
            
        }
        
        [QuerySqlField(IsIndexed = true)]
        public long AccountId { get; set; }
        [QuerySqlField]
        public bool Active { get; set; } = true;
    }
}