import MainLayout from "@/Components/Layout/MainLayout";

export default function Students() {
    return <h1>Student List</h1>;
}
Students.layout = (page) => <MainLayout>{page}</MainLayout>;
