using Apache.Ignite.Core.Cache.Configuration;
using System.ComponentModel.DataAnnotations;

namespace Domain
{
    public class Person
    {

        public Person()
        {
            
        }
        
        [QuerySqlField(IsIndexed = true)]
        public long Id { get; set; }
        
        [Required]
        [QuerySqlField]
        public string Name { get; set; }
        
        [Required]
        [QuerySqlField]
        public string Email { get; set; }
        
        [QuerySqlField]
        public string PhoneNumber { get; set; }
        
        [QuerySqlField]
        public string Description { get; set; }
        [QuerySqlField]
        
        public string PublicIdNumber { get; set; }
        
    }
}