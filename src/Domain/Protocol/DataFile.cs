using System;

namespace Domain.Protocol
{
    public class DataFile
    {
        public DateTime CreationDateTime {get; set;}
        public float Version { get; set; }
        
        public string FileName { get; set; }
        
        public Boolean IsEncrypted { get; set; }
        
        public string UsersData { get; set; }
        
        public string AccountsData { get; set; }
        
        public string UsersGroupsData { get; set; }
        
        public string RolesData { get; set; }
        
        public string EnvironmentsData { get; set; }
        
    }
}