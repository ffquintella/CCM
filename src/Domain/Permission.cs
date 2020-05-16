namespace Domain
{
    public class Permission
    {
        public long Id { get; set; }
        public int Type { get; set; }
        public int Consent { get; set; }
        public long EnvironmentId { get; set; }
        public long GroupId { get; set; }
        public long OwnerId { get; set; }
    }
}